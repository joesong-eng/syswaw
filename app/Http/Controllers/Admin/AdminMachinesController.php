<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\MachineAuthKey;
use App\Models\Arcade;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminMachinesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        // 優化：移除了前面被覆蓋的無效查詢邏輯，保留有效部分。
        $machinesQuery = Machine::query()->with(['arcade', 'owner', 'machineAuthKey', 'creator']);
        $machines = $machinesQuery->latest()->paginate(15);

        $arcades = Arcade::orderBy('name')->get();

        // 優化：合併了多個用戶查詢，提高效率。
        $potentialMachineOwners = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['arcade-owner', 'machine-owner', 'admin']);
        })->orderBy('name')->get();

        $availableAuthKeys = MachineAuthKey::whereNull('machine_id')
            ->orderBy('chip_hardware_id') // 改為按 chip_hardware_id 排序
            ->get();
        $admins = User::role('admin')->get();
        $arcadeOwners = User::role('arcade-owner')->get();
        $machineOwners = User::role('machine-owner')->get();

        return view('admin.machines.index', compact('machines', 'arcades', 'potentialMachineOwners', 'availableAuthKeys', 'admins', 'arcadeOwners', 'machineOwners'));
    }

    public function store(Request $request)
    {
        Log::info('提交的請求資料:', ['request_data' => $request->all(), 'chip_hardware_id' => $request->input('chip_hardware_id')]);

        // 獲取 machine_category 的有效鍵
        $allowedMachineCategories = array_keys(config('machines.templates', []));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_category' => ['required', 'string', Rule::in($allowedMachineCategories)],
            'machine_type' => 'nullable|string|max:255', // machine_type 現在是可選的詳細型號
            'arcade_id' => 'required|exists:arcades,id',
            'chip_hardware_id' => [
                'required',
                'string',
                'max:10',
                'regex:/^[a-zA-Z0-9_\-\.@]{1,10}$/',
                Rule::unique('machine_auth_keys', 'chip_hardware_id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'owner_id' => 'required|integer|exists:users,id',
            'coin_input_value' => 'nullable|numeric|min:0',
            'credit_button_value' => 'nullable|numeric|min:0',
            'payout_button_value' => 'nullable|numeric|min:0',
            'payout_type' => ['nullable', 'string', Rule::in(['points', 'tickets', 'coins', 'ball', 'prize', 'none', 'money_slot'])],
            'payout_unit_value' => 'nullable|numeric|min:0',
            'revenue_split' => 'nullable|numeric|min:0|max:100',
            'bill_acceptor_enabled' => 'boolean',
            'bill_currency' => 'nullable|string|max:3',
            'accepted_denominations' => 'nullable|array',
            'share_pct' => 'nullable|numeric|min:0|max:100',
            'ui_language' => 'nullable|string|max:255',
            'auto_shutdown_seconds' => 'nullable|integer|min:0',
            'accepted_denominations.*' => 'nullable|numeric',
        ], [
            'chip_hardware_id.regex' => '通訊卡 ID 格式無效，請使用 1-10 位的英文、數字、下滑線、連字符、點或 @ 符號。',
            'chip_hardware_id.unique' => '此通訊卡 ID 已被使用，請選擇另一個。',
            'machine_category.required' => '必須選擇一個主要營運模式。',
            'machine_category.in' => '選擇的營運模式無效。',
        ]);

        try {
            DB::beginTransaction();
            $authKey = MachineAuthKey::create([
                'auth_key' => Str::random(8),
                'chip_hardware_id' => $validated['chip_hardware_id'],
                'owner_id' => $validated['owner_id'],
                'created_by' => Auth::id(),
                'status' => 'active',
                'expires_at' => now()->addHours(24),
            ]);

            $machine = Machine::create([
                'name' => $validated['name'],
                'machine_category' => $validated['machine_category'], // 新增欄位
                'machine_type' => $validated['machine_type'] ?? null, // 更新欄位
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'created_by' => Auth::id(),
                'auth_key_id' => $authKey->id,
                'status' => ['state' => 'active', 'message' => 'Machine created successfully.'],
                'is_active' => true,
                'coin_input_value' => $validated['coin_input_value'] ?? null,
                'credit_button_value' => $validated['credit_button_value'] ?? null,
                'payout_button_value' => $validated['payout_button_value'] ?? null,
                'payout_type' => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $request->boolean('bill_acceptor_enabled'),
                'bill_currency' => $validated['bill_currency'] ?? null,
                'accepted_denominations' => array_values(array_filter($validated['accepted_denominations'] ?? [], fn($v) => $v !== null)),
                'share_pct' => $validated['share_pct'] ?? null,
                'ui_language' => $validated['ui_language'] ?? 'en',
                'auto_shutdown_seconds' => $validated['auto_shutdown_seconds'] ?? 300,
            ]);

            $authKey->machine_id = $machine->id;
            $authKey->save();

            DB::commit();
            return redirect()->route('admin.machines.index')
                ->with('success', __('msg.machine_added_successfully'))
                ->with('auth_key', $authKey->auth_key);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('創建機器失敗: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'request_data' => $request->all()]);
            return redirect()->back()
                ->with('error', __('msg.error_creating_machine') . ': ' . $e->getMessage())
                ->withInput();
        }
    }
    public function update(Request $request, Machine $machine)
    {

        Log::info('AdminMachinesController@update started.', ['machine_id' => $machine->id, 'request_data' => $request->all()]);
        if (!$machine->exists) {
            Log::error('Machine model binding failed in update method.', ['route_parameters' => $request->route()->parameters()]);
            return response()->json(['message' => '無法找到指定的機器記錄進行更新。'], 404);
        }

        // 獲取 machine_category 的有效鍵
        $allowedMachineCategories = array_keys(config('machines.templates', []));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_category' => ['required', 'string', Rule::in($allowedMachineCategories)],
            'machine_type' => 'nullable|string|max:255', // machine_type 現在是可選的詳細型號
            'arcade_id' => 'required|exists:arcades,id',
            'owner_id' => 'required|integer|exists:users,id',
            'coin_input_value' => 'nullable|numeric|min:0',
            'credit_button_value' => 'nullable|numeric|min:0',
            'payout_button_value' => 'nullable|numeric|min:0',
            'payout_type' => ['nullable', 'string', Rule::in(['points', 'tickets', 'coins', 'ball', 'prize', 'none', 'money_slot'])],
            'payout_unit_value' => 'nullable|numeric|min:0',
            'revenue_split' => 'nullable|numeric|min:0|max:100',
            'bill_acceptor_enabled' => 'boolean',
            'bill_currency' => 'nullable|string|max:3',
            'accepted_denominations' => 'nullable|array',
            'share_pct' => 'nullable|numeric|min:0|max:100',
            'ui_language' => 'nullable|string|max:255',
            'auto_shutdown_seconds' => 'nullable|integer|min:0',
            'accepted_denominations.*' => 'nullable|numeric',
        ], [
            'machine_category.required' => '必須選擇一個主要營運模式。',
            'machine_category.in' => '選擇的營運模式無效。',
        ]);

        try {
            DB::beginTransaction();
            $machineData = [
                'name' => $validated['name'],
                'machine_category' => $validated['machine_category'], // 新增欄位
                'machine_type' => $validated['machine_type'] ?? null, // 更新欄位
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'coin_input_value' => $validated['coin_input_value'] ?? null,
                'credit_button_value' => $validated['credit_button_value'] ?? null,
                'payout_button_value' => $validated['payout_button_value'] ?? null,
                'payout_type' => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $request->boolean('bill_acceptor_enabled'),
                'bill_currency' => $validated['bill_currency'] ?? null,
                'accepted_denominations' => array_values(array_filter($validated['accepted_denominations'] ?? [], fn($v) => $v !== null)),
                'share_pct' => $validated['share_pct'] ?? null,
                'ui_language' => $validated['ui_language'] ?? $machine->ui_language,
                'auto_shutdown_seconds' => $validated['auto_shutdown_seconds'] ?? $machine->auto_shutdown_seconds,
            ];
            $machine->update($machineData);
            DB::commit();
            return redirect()->route('admin.machines.index')->with('success', __('msg.machine_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新機器失敗: ' . $e->getMessage(), ['machine_id' => $machine->id, 'request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Machine $machine)
    {
        Log::info('AdminMachinesController@edit called for machine ID:', ['machine_id' => $machine->id]);
        $machine->load(['arcade', 'owner', 'machineAuthKey']);
        $arcades = Arcade::orderBy('name')->get();
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['machine-owner', 'admin']);
        })->orderBy('name')->get();

        return view('admin.machines.edit', compact('machine', 'arcades', 'users'));
    }



    public function toggleActive(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);
        $machine->is_active = $request->input('is_active', 0);
        $machine->save();
        Log::info('Machine active status toggled.', ['machine_id' => $machine->id, 'is_active' => $machine->is_active]);
        return redirect()->route('admin.machines.index')->with('success', __('msg.machine_status_updated_successfully'));
    }

    public function destroy(Machine $machine)
    {
        Log::info('AdminMachinesController@destroy called.', ['machine_id' => $machine->id]);
        try {
            DB::transaction(function () use ($machine) {
                if ($machine->machineAuthKey) {
                    Log::info('Unbinding MachineAuthKey during machine deletion.', ['machine_id' => $machine->id, 'auth_key_id' => $machine->machineAuthKey->id]);
                    $machine->machineAuthKey()->update(['status' => 'pending', 'machine_id' => null, 'chip_hardware_id' => null]);
                }
                $machine->delete();
            });
            Log::info('Machine deleted successfully.', ['machine_id' => $machine->id]);
            return redirect()->route('admin.machines.index')->with('success', __('msg.machine_deleted_successfully'));
        } catch (\Exception $e) {
            Log::error('Error deleting machine: ' . $e->getMessage(), ['machine_id' => $machine->id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.machines.index')->with('error', __('msg.error_deleting_machine') . ': ' . $e->getMessage());
        }
    }

    public function printKeys(Request $request)
    {
        Log::info('AdminMachinesController@printKeys called by admin.', ['user_id' => Auth::id()]);
        $authKeys = MachineAuthKey::whereNull('machine_id')->get();
        $qrCodes = $authKeys->map(function ($key) {
            $qrCode = QrCode::size(200)->generate('@' . $key->chip_hardware_id . ':' . $key->auth_key); // 生成 @iot_XXXX:auth_key 格式
            return [
                'chip_hardware_id' => $key->chip_hardware_id,
                'auth_key' => $key->auth_key,
                'qr_code' => $qrCode,
            ];
        });
        return view('admin.machines.print_keys', compact('qrCodes'));
    }

    // 移除：這個私有方法從未被呼叫，是無用的死代碼。
}
