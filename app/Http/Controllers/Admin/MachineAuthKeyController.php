<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MachineAuthKey;
use App\Models\User;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MachineAuthKeyController extends Controller
{
    public function index(Request $request)
    {
        $query = MachineAuthKey::query();

        // Handle Sorting
        $sortColumn = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'asc');

        $allowedSortColumns = ['id', 'auth_key', 'chip_hardware_id', 'expires_at', 'status', 'created_at', 'owner_id', 'machine_id'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at';
        }

        $query->with(['creator', 'owner', 'machine']);
        if ($sortColumn === 'owner_id' && $request->has('sort_by_owner_name')) {
            $query->orderBy('owner_id', $sortDirection);
        } elseif ($sortColumn === 'machine_id' && $request->has('sort_by_machine_name')) {
            $query->orderBy('machine_id', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }
        $machineAuthKeys = $query->paginate(15);
        $this->clear_expire_key();
        return view('admin.machine_auth_keys.index', compact('machineAuthKeys'));
    }

    public function create()
    {
        return redirect()->route('admin.machine_auth_keys.index')->with('info', '請使用列表頁的「新增金鑰」按鈕進行快速新增。');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', Rule::in([1, 10, 20, 30, 50])],
        ]);

        $quantity = $validated['quantity'];
        $createdCount = 0;
        try {
            for ($i = 0; $i < $quantity; $i++) {
                MachineAuthKey::create([
                    'auth_key'         => Str::random(8), // 8 位隨機字串，如 8f6b3e4d
                    'chip_hardware_id' => 'iot_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT), // 生成 iot_XXXX 格式
                    'owner_id'         => Auth::id(),
                    'created_by'       => Auth::id(),
                    'expires_at'       => now()->addHours(24),
                    'status'           => 'pending',
                    'printed'          => false,
                    'machine_id'       => null,
                ]);
                $createdCount++;
            }

            return redirect()->route('admin.machine_auth_keys.index')
                ->with('success', "已成功創建 {$createdCount} 個機器驗證金鑰。");
        } catch (\Exception $e) {
            Log::error("創建 {$quantity} 個 MachineAuthKey 時出錯 (已創建 {$createdCount} 個): " . $e->getMessage());
            return back()->with('error', '創建金鑰失敗，請再試一次。');
        }
    }

    public function show(MachineAuthKey $machineAuthKey)
    {
        return redirect()->route('admin.machine_auth_keys.edit', $machineAuthKey);
    }

    public function edit(MachineAuthKey $machineAuthKey)
    {
        $owners = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['machine-owner', 'admin', 'arcade-owner']);
        })->orderBy('name')->get();

        $availableMachines = Machine::where(function ($query) use ($machineAuthKey) {
            $query->whereNull('auth_key_id')
                ->orWhere('auth_key_id', $machineAuthKey->id);
        })->orderBy('name')->get();

        return view('admin.machine_auth_keys.edit', compact('machineAuthKey', 'owners', 'availableMachines'));
    }

    public function update(Request $request, MachineAuthKey $machineAuthKey)
    {
        $validatedData = $request->validate([
            'owner_id' => 'required|exists:users,id',
            'chip_hardware_id' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^iot_\d{4}$/', // 限制格式為 iot_XXXX
                Rule::unique('machine_auth_keys', 'chip_hardware_id')->ignore($machineAuthKey->id),
            ],
            'expires_at' => 'nullable|date',
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'inactive'])],
            'machine_id' => 'nullable|exists:machines,id',
            'printed' => 'sometimes|boolean',
        ]);

        try {
            if ($request->filled('machine_id') && $machineAuthKey->machine_id && $machineAuthKey->machine_id != $request->machine_id) {
                $oldMachine = Machine::find($machineAuthKey->machine_id);
                if ($oldMachine) {
                    $oldMachine->auth_key_id = null;
                    $oldMachine->save();
                }
            }

            $machineAuthKey->owner_id = $validatedData['owner_id'];
            $machineAuthKey->chip_hardware_id = $validatedData['chip_hardware_id'];
            $machineAuthKey->expires_at = $validatedData['expires_at'];
            $machineAuthKey->status = $validatedData['status'];
            $machineAuthKey->printed = $request->boolean('printed');
            $machineAuthKey->save();

            if ($request->filled('machine_id')) {
                $newMachine = Machine::find($request->machine_id);
                if ($newMachine && $newMachine->auth_key_id != $machineAuthKey->id) {
                    if ($newMachine->machineAuthKey) {
                        $newMachine->machineAuthKey->machine_id = null;
                        $newMachine->machineAuthKey->save();
                    }
                    $newMachine->auth_key_id = $machineAuthKey->id;
                    $newMachine->save();
                }
            }

            return redirect()->route('admin.machine_auth_keys.index')
                ->with('success', '機器驗證金鑰已成功更新。');
        } catch (\Exception $e) {
            Log::error('更新 MachineAuthKey 時出錯: ' . $e->getMessage(), ['key_id' => $machineAuthKey->id, 'request_data' => $request->all()]);
            return back()->withInput()->with('error', '更新金鑰失敗，請再試一次。');
        }
    }

    public function destroy(MachineAuthKey $machineAuthKey)
    {
        try {
            if ($machineAuthKey->machine_id) {
                $machine = Machine::find($machineAuthKey->machine_id);
                if ($machine) {
                    $machine->auth_key_id = null;
                    $machine->save();
                }
            }
            $machineAuthKey->delete();
            return redirect()->route('admin.machine_auth_keys.index')
                ->with('success', '機器驗證金鑰已成功刪除。');
        } catch (\Exception $e) {
            Log::error('刪除 MachineAuthKey 時出錯: ' . $e->getMessage(), ['key_id' => $machineAuthKey->id]);
            return back()->with('error', '刪除金鑰失敗，請再試一次。');
        }
    }

    public function printKeys(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);
        if (empty($selectedIds)) {
            return redirect()->route('admin.machine_auth_keys.index')->with('error', '請先選擇要列印的金鑰。');
        }

        $chipKeys = MachineAuthKey::whereIn('id', $selectedIds)->get();

        if ($chipKeys->isEmpty()) {
            return redirect()->route('admin.machine_auth_keys.index')->with('error', '選擇的金鑰不存在。');
        }
        MachineAuthKey::whereIn('id', $selectedIds)->update(['printed' => true]);

        return view('admin.machine_auth_keys.print_keys', compact('chipKeys'));
    }

    public function generateSingleKey(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->id;
        try {
            $authKeyString = Str::random(8);
            $newKey = MachineAuthKey::create([
                'auth_key' => $authKeyString,
                'chip_hardware_id' => 'iot_' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT), // 生成 iot_XXXX 格式
                'owner_id' => $ownerId,
                'created_by' => $user->id,
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            return response()->json(['success' => true, 'auth_key' => $newKey->auth_key, 'message' => '金鑰生成成功！']);
        } catch (\Exception $e) {
            Log::error("Error generating single MachineAuthKey for arcade owner: " . $e->getMessage(), ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => '生成金鑰時發生錯誤: ' . $e->getMessage()], 500);
        }
    }

    private function clear_expire_key()
    {
        $deleted = MachineAuthKey::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->whereNull('machine_id')
            ->delete();
        Log::info("已刪除 {$deleted} 個過期且未使用的金鑰");
    }
}
