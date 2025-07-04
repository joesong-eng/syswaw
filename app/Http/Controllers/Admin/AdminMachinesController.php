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
        $user = Auth::user();
        $queryMachines = Machine::query();
        $ownerId = $user->hasRole('arcade-owner') ? $user->id : ($user->hasRole('arcade-staff') ? $user->parent_id : null);
        if ($ownerId) {
            $arcadeIds = Arcade::where('owner_id', $ownerId)->pluck('id');
            $queryMachines->whereIn('arcade_id', $arcadeIds);
        }
        $queryMachines->with(['arcade', 'owner', 'machineAuthKey']);
        $machines = $queryMachines->orderBy('created_at', 'desc')->paginate(15);
        $arcades = $ownerId ? Arcade::where('owner_id', $ownerId)->get(['id', 'name', 'currency']) : collect();
        $potentialMachineOwners = User::orderBy('name')->get();
        $availableAuthKeys = $ownerId ? MachineAuthKey::where('owner_id', $ownerId)
            ->whereNull('machine_id')
            ->where('status', 'pending')
            ->get() : collect();

        $machinesQuery = Machine::query()->with(['arcade', 'owner', 'machineAuthKey', 'creator']);
        $machines = $machinesQuery->latest()->paginate(15);
        $arcades = Arcade::orderBy('name')->get();
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['arcade-owner', 'machine-owner', 'admin']);
        })->orderBy('name')->get();
        $availableAuthKeys = MachineAuthKey::whereNull('machine_id')
            ->orderBy('chip_hardware_id') // 改為按 chip_hardware_id 排序
            ->get();
        $admins = User::role('admin')->get();
        $arcadeOwners = User::role('arcade-owner')->get();
        $machineOwners = User::role('machine-owner')->get();

        return view('admin.machines.index', compact('admins', 'arcadeOwners', 'machineOwners', 'machines', 'arcades', 'potentialMachineOwners', 'availableAuthKeys'));
    }

    public function store(Request $request)
    {
        Log::info('提交的請求資料:', ['request_data' => $request->all(), 'chip_hardware_id' => $request->input('chip_hardware_id')]);
        $allowedMachineTypes = array_keys(config('machines.types', []));
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_type' => ['required', 'string', Rule::in($allowedMachineTypes)],
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
            'accepted_denominations.*' => 'nullable|numeric',
        ], [
            'chip_hardware_id.regex' => '通訊卡 ID 格式無效，請使用 1-10 位的英文、數字、下滑線、連字符、點或 @ 符號。',
            'chip_hardware_id.unique' => '此通訊卡 ID 已被使用，請選擇另一個。',
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
                'machine_type' => $validated['machine_type'],
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'created_by' => Auth::id(),
                'auth_key_id' => $authKey->id,
                'status' => 'active',
                'is_active' => true,
                'coin_input_value' => $validated['coin_input_value'] ?? null,
                'credit_button_value' => $validated['credit_button_value'] ?? null,
                'payout_button_value' => $validated['payout_button_value'] ?? null,
                'payout_type' => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $validated['machine_type'] === 'money_slot' ? true : ($validated['bill_acceptor_enabled'] ?? false),
                'bill_currency' => $validated['bill_currency'] ?? null,
                'accepted_denominations' => $validated['accepted_denominations'] ? json_encode($validated['accepted_denominations']) : null,
                'share_pct' => $validated['share_pct'] ?? null,
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

    public function update(Request $request, Machine $machine)
    {
        Log::info('AdminMachinesController@update started.', ['machine_id' => $machine->id, 'request_data' => $request->all()]);
        if (!$machine->exists) {
            Log::error('Machine model binding failed in update method.', ['route_parameters' => $request->route()->parameters()]);
            return response()->json(['message' => '無法找到指定的機器記錄進行更新。'], 404);
        }

        $allowedMachineTypes = array_keys(config('machines.types', []));
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_type' => ['required', 'string', Rule::in($allowedMachineTypes)],
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
            'accepted_denominations.*' => 'nullable|numeric',
        ], [
            'chip_hardware_id.regex' => '通訊卡 ID 格式無效，請使用 1-10 位的英文、數字、下滑線、連字符、點或 @ 符號。',
            'chip_hardware_id.unique' => '此通訊卡 ID 已被使用，請選擇另一個。',
        ]);

        try {
            DB::beginTransaction();
            $machineData = [
                'name' => $validated['name'],
                'machine_type' => $validated['machine_type'],
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'coin_input_value' => $validated['coin_input_value'] ?? null,
                'credit_button_value' => $validated['credit_button_value'] ?? null,
                'payout_button_value' => $validated['payout_button_value'] ?? null,
                'payout_type' => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $validated['bill_acceptor_enabled'] ?? false,
                'bill_currency' => $validated['bill_currency'] ?? null,
                'accepted_denominations' => isset($validated['accepted_denominations']) && is_array($validated['accepted_denominations']) ? json_encode($validated['accepted_denominations']) : null,
                'share_pct' => $validated['share_pct'] ?? null,
            ];

            if ($validated['machine_type'] === 'money_slot') {
                $machineData['bill_acceptor_enabled'] = true;
            }

            $machine->update($machineData);
            DB::commit();
            return redirect()->route('admin.machines.index')->with('success', __('msg.machine_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新機器失敗: ' . $e->getMessage(), ['machine_id' => $machine->id, 'request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage())->withInput();
        }
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

    private function validateChipKeyAndHardwareIdForStore(\Illuminate\Contracts\Validation\Validator $validator, Request $request): void
    {
        $chipHardwareId = $request->input('chip_hardware_id');
        if (empty($chipHardwareId)) {
            $validator->errors()->add('chip_hardware_id', __('validation.required', ['attribute' => __('msg.chip_hardware_id')]));
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9_\-\.@]{1,10}$/', $chipHardwareId)) {
            $validator->errors()->add('chip_hardware_id', __('validation.regex', ['attribute' => __('msg.chip_hardware_id')]));
            return;
        }
        $existingKeyWithHwId = MachineAuthKey::where('chip_hardware_id', $chipHardwareId)
            ->whereNull('deleted_at')
            ->first();
        if ($existingKeyWithHwId) {
            $validator->errors()->add('chip_hardware_id', __('validation.custom.chip_hardware_id.unique'));
        }
    }
}
