<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Validator; // 引入 Validator Facade
use Illuminate\Support\Facades\Redis; // 引入 Redis Facade

use function compact;

class MachinesController extends Controller
{
    /**
     * Display a listing of the resource based on user role.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $machinesQuery = Machine::query()->with(['arcade', 'owner', 'machineAuthKey', 'creator']); // 預載入關聯
        $arcadesQuery = Arcade::query();
        $usersQuery = User::query();
        $returnUrl = 'admin.machines.index'; // 預設為 admin 視圖

        if ($user->hasRole('admin')) {
            // 如果是 admin，顯示所有機器、Arcade 和用戶
            $arcades = $arcadesQuery->orderBy('name')->get();
            $users = $usersQuery->whereHas('roles', function ($query) {
                $query->whereIn('name', ['arcade-owner', 'machine-owner', 'admin']); // 確保這些角色存在
            })->orderBy('name')->get();
            // $machines 的查詢不需要額外條件，Admin 看全部

        } elseif ($user->hasRole('arcade-owner')) {
            // Arcade Owner: 看自己遊藝場下的機器
            $arcadeIds = $arcadesQuery->where('owner_id', $user->id)->pluck('id');
            $machinesQuery->whereIn('arcade_id', $arcadeIds);
            $arcades = Arcade::where('owner_id', $user->id)->orderBy('name')->get(); // 遊藝場老闆自己的遊藝場
            $users = $usersQuery->where('id', $user->id) // 遊藝場老闆自己
                ->orWhere('parent_id', $user->id) // 及其下屬員工
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['machine-owner', 'arcade-staff'])) // 篩選特定角色的下屬
                ->orderBy('name')->get();
            $returnUrl = 'arcade.machines.index'; // Arcade Owner 的視圖

        } elseif ($user->hasRole('arcade-staff')) {
            // Arcade Staff: 看其老闆遊藝場下的機器
            $ownerId = $user->parent_id; // 獲取上級 ID
            if ($ownerId) {
                $arcadeIds = $arcadesQuery->where('owner_id', $ownerId)->pluck('id');
                $machinesQuery->whereIn('arcade_id', $arcadeIds);
                $arcades = Arcade::where('owner_id', $ownerId)->orderBy('name')->get();
                $users = $usersQuery->where('id', $ownerId) // 遊藝場老闆
                    ->orWhere('parent_id', $ownerId) // 及其他下屬員工
                    ->orderBy('name')->get();
            } else {
                $machinesQuery->whereRaw('1 = 0'); // 無效查詢，返回空集合
                $arcades = collect();
                $users = collect();
            }
            $returnUrl = 'arcade.machines.index'; // Arcade Staff 的視圖

        } elseif ($user->hasRole('machine-owner')) {
            // Machine Owner: 看自己擁有的機器
            $machinesQuery->where('owner_id', $user->id);
            $arcades = Arcade::whereHas('machines', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->distinct()->orderBy('name')->get(); // 獲取這些機器所在的遊藝場
            $users = $usersQuery->where('id', $user->id) // 機器老闆自己
                ->orWhere('parent_id', $user->id) // 及其下屬員工 (如果 machine-owner 也有 staff)
                ->orderBy('name')->get();
            $returnUrl = 'machine.machines.index'; // Machine Owner 的視圖

        } elseif ($user->hasRole('machine-staff')) {
            // Machine Staff: 看其老闆擁有的機器
            $ownerId = $user->parent_id; // 獲取上級 ID
            if ($ownerId) {
                $machinesQuery->where('owner_id', $ownerId);
                $arcades = Arcade::whereHas('machines', function ($q) use ($ownerId) {
                    $q->where('owner_id', $ownerId);
                })->distinct()->orderBy('name')->get();
                $users = $usersQuery->where('id', $ownerId) // 機器老闆
                    ->orWhere('parent_id', $ownerId) // 及其他下屬員工
                    ->orderBy('name')->get();
            } else {
                $machinesQuery->whereRaw('1 = 0'); // 無效查詢，返回空集合
                $arcades = collect();
                $users = collect();
            }
            $returnUrl = 'machine.machines.index'; // Machine Staff 的視圖

        } else {
            // 其他角色無權查看
            abort(403, 'Unauthorized action.');
        }

        // 執行機器查詢並分頁
        $machines = $machinesQuery->latest()->paginate(15);

        // 獲取可用的 (未綁定機器的) MachineAuthKey
        // 這個列表對於所有需要新增機器的角色都是一樣的
        $availableAuthKeys = MachineAuthKey::whereNull('machine_id')
            ->orderBy('auth_key')
            ->get();

        // 返回對應視圖，並傳遞所有需要的數據
        return view($returnUrl, compact('machines', 'arcades', 'users', 'availableAuthKeys'));
    }

    /**
     * @deprecated This method seems to use old logic ('token', 'used' fields on Machine)
     *             that might conflict with MachineAuthKey. Review and update required.
     */
    public function addMachine(Request $request)
    {
        // This method seems to be for updating an existing machine based on a token,
        // not for creating a new one. Its logic needs to be reviewed against the
        // new MachineAuthKey and Machine models.
        Log::warning('Deprecated addMachine method called.', ['request_data' => $request->all()]);

        //驗證請求資料
        $validated = $request->validate([
            'machine_id' => 'required|string|max:8|regex:/^[a-zA-Z0-9]+$/', // This might be the physical ID, not the DB primary key
            'token' => 'required|string|exists:machine_auth_keys,auth_key', // Validate key exists
            'mname' => 'nullable|string',
            'machine_type' => 'nullable|string',
            'owner_id' => 'required|exists:users,id',
        ]);

        try {
            // Find the MachineAuthKey first
            $authKey = MachineAuthKey::where('auth_key', $validated['token'])->firstOrFail();

            // Find the Machine associated with this AuthKey
            $machine = $authKey->machine; // Assumes MachineAuthKey has a belongsTo machine() relationship

            if (!$machine) {
                // If the machine is not found via the auth key, it means the key is not bound.
                // This method seems intended to update an *existing* machine.
                // If the goal is to bind a key to a machine, the logic needs to be different.
                Log::error('addMachine: Auth key not bound to any machine.', ['auth_key_id' => $authKey->id]);
                return redirect()->back()->withErrors(['token' => '該金鑰尚未綁定任何機器。']);
            }

            // Check if the provided machine_id (physical ID) matches the one on the AuthKey if it exists
            // Or if the AuthKey is already bound to a different physical ID
            if ($authKey->chip_hardware_id && $authKey->chip_hardware_id !== $validated['machine_id']) {
                Log::warning('addMachine: Provided machine_id does not match auth key hardware ID.', [
                    'auth_key_id' => $authKey->id,
                    'provided_id' => $validated['machine_id'],
                    'auth_key_hw_id' => $authKey->chip_hardware_id
                ]);
                return redirect()->back()->withErrors(['machine_id' => '提供的硬體 ID 與金鑰記錄的硬體 ID 不符。']);
            } elseif (!$authKey->chip_hardware_id) {
                // If auth key doesn't have a hardware ID, update it
                $authKey->chip_hardware_id = $validated['machine_id'];
                $authKey->save();
            }


            // Update the Machine record
            $machine->name = $validated['mname'] ?? $machine->name;
            $machine->machine_type = $validated['machine_type'] ?? $machine->machine_type;
            $machine->owner_id = $validated['owner_id'];
            $machine->is_active = true; // Assuming this action activates the machine
            // The 'status' field might need a different structure (e.g., JSON)
            // $machine->status = ['status_key' => 'Normal']; // Example update

            $machine->save();

            Log::info('Machine updated via addMachine (deprecated).', ['machine_id' => $machine->id, 'auth_key_id' => $authKey->id]);
            return redirect()->back()->with('success', '機器資訊已成功更新。');
        } catch (\Exception $e) {
            Log::error('Error in addMachine (deprecated): ' . $e->getMessage(), ['request_data' => $request->all(), 'exception' => $e]);
            return redirect()->back()->with('error', '更新機器資訊失敗：' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store a newly created machine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::info('MachinesController@store incoming request data:', $request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'auth_key' => 'required|string|exists:machine_auth_keys,auth_key',
            'chip_hardware_id' => 'required|string|max:255',
            'machine_type' => ['required', 'string', Rule::in(['pinball', 'claw', 'points_redemption', 'ticket_redemption', 'normally', 'money_slot'])],
            'arcade_id' => 'required|exists:arcades,id',
            'owner_id' => 'required|exists:users,id',
            'credit_value' => 'nullable|numeric|min:0',
            'balls_per_credit' => 'nullable|integer|min:1',
            'points_per_credit_action' => 'nullable|integer|min:1',
            'payout_type' => ['nullable', 'string', Rule::in(['points', 'tickets', 'tokens', 'prize', 'none', 'money_slot'])],
            'payout_unit_value' => 'nullable|numeric|min:0',
        ]);

        // 自定義驗證邏輯，用於 auth_key 和 chip_hardware_id 的複雜檢查
        $validator->after(function ($validator) use ($request) {
            $this->validateChipKeyAndHardwareIdForStore($validator, $request);
        });

        if ($validator->fails()) {
            Log::warning('MachinesController@store validation failed.', ['errors' => $validator->errors()->all()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();
        Log::info('MachinesController@store validation passed.', ['validated_data' => $validatedData]);

        try {
            DB::transaction(function () use ($validatedData) {
                // 找到並更新 MachineAuthKey
                $machineAuthKey = MachineAuthKey::where('auth_key', $validatedData['auth_key'])->firstOrFail();
                $machineAuthKey->chip_hardware_id = $validatedData['chip_hardware_id'];
                $machineAuthKey->status = 'active'; // 將金鑰狀態設為 active
                // $machineAuthKey->owner_id = $validatedData['owner_id']; // 通常 MachineAuthKey 的 owner_id 在創建時設定，代表金鑰的擁有者
                $machineAuthKey->save();

                // 創建 Machine 實例
                $machine = new Machine();
                $machine->name = $validatedData['name'];
                $machine->machine_type = $validatedData['machine_type'];
                $machine->arcade_id = $validatedData['arcade_id'];
                $machine->owner_id = $validatedData['owner_id'];
                $machine->created_by = Auth::id();
                $machine->auth_key_id = $machineAuthKey->id; // 關聯 MachineAuthKey
                $machine->is_active = true; // 新增機器預設為啟用 (或 false 待審核)

                // 設定覆寫值
                $machine->credit_value = $validatedData['credit_value'] ?? null;
                $machine->balls_per_credit = $validatedData['balls_per_credit'] ?? null;
                $machine->points_per_credit_action = $validatedData['points_per_credit_action'] ?? null;
                $machine->payout_type = $validatedData['payout_type'] ?? null;
                $machine->payout_unit_value = $validatedData['payout_unit_value'] ?? null;

                $machine->save();

                // 更新 MachineAuthKey 中的 machine_id (確保雙向關聯)
                $machineAuthKey->machine_id = $machine->id;
                $machineAuthKey->save();

                Log::info('Machine created and AuthKey updated successfully.', ['machine_id' => $machine->id, 'auth_key_id' => $machineAuthKey->id]);
            });

            return redirect()->route('admin.machines.index')->with('success', __('msg.machine') . '「' . $validatedData['name'] . '」' . __('msg.create') . __('msg.success'));
        } catch (\Exception $e) {
            Log::error('Error adding machine: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', __('msg.error_adding_machine') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Helper function to validate auth_key and chip_hardware_id for store method.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @param Request $request
     * @return void
     */
    private function validateChipKeyAndHardwareIdForStore(\Illuminate\Contracts\Validation\Validator $validator, Request $request): void
    {
        $chipKeyString = $request->input('auth_key');
        $chipHardwareId = $request->input('chip_hardware_id');

        // Basic required validation should catch empty values, but check again for safety
        if (empty($chipKeyString) || empty($chipHardwareId)) {
            return;
        }

        $authKey = MachineAuthKey::where('auth_key', $chipKeyString)->first();

        if (!$authKey) {
            // This should be caught by 'exists:machine_auth_keys,auth_key' rule, but add error just in case
            $validator->errors()->add('auth_key', __('validation.exists', ['attribute' => __('msg.chip_token')]));
            return;
        }

        // 1. 檢查金鑰是否已被其他機器綁定
        if ($authKey->machine_id !== null) {
            $validator->errors()->add('auth_key', __('msg.chip_key_already_used'));
        }

        // 2. 檢查 chip_hardware_id 是否已被其他金鑰使用 (排除當前選中的金鑰)
        $existingKeyWithHwId = MachineAuthKey::where('chip_hardware_id', $chipHardwareId)
            ->where('id', '!=', $authKey->id) // 排除當前金鑰
            ->first();
        if ($existingKeyWithHwId) {
            // 使用自定義的驗證訊息
            $validator->errors()->add('chip_hardware_id', __('validation.custom.chip_hardware_id.unique'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * (Typically handled by a modal in the index view)
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Machine $machine)
    {
        // This method might not be needed if editing is purely modal-based on the index page.
        // If it is used, ensure it loads necessary data for a dedicated edit page.
        Log::info('MachinesController@edit called for machine ID:', ['machine_id' => $machine->id]);

        // Load relationships needed for the edit form/modal
        $machine->load(['arcade', 'owner', 'machineAuthKey']);

        // Get data for dropdowns in the edit form/modal
        $arcades = Arcade::orderBy('name')->get();
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['machine-owner', 'admin']); // Users who can be machine owners
        })->orderBy('name')->get();

        // Note: availableAuthKeys is typically not needed for editing a bound machine,
        // as changing the key is a separate process (unbind/bind new).

        // Assuming you have an edit view like 'admin.machines.edit'
        // If using a modal, this method might not be directly routed to,
        // but the data might be prepared on the index page or fetched via AJAX.
        // If this method *is* routed to, you'd return a view like:
        // return view('admin.machines.edit', compact('machine', 'arcades', 'users'));

        // For now, we'll assume modal editing on index, so this method might be unused.
        // If you need a dedicated edit page, uncomment the return view line above.
        // If this method is called unexpectedly, you might redirect or return an error.
        return redirect()->route('admin.machines.index')->with('info', '編輯功能通常在列表頁面直接操作。');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Machine $machine)
    {
        // Authorization check (example: only admin or owner can update)
        // $user = Auth::user();
        // if (!$user->hasRole('admin') && $machine->owner_id !== $user->id) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (!$machine->exists) {
            Log::error('Machine model binding failed in update method.', ['route_parameters' => $request->route()->parameters()]);
            return redirect()->route('admin.machines.index')->with('error', '無法找到指定的機器記錄進行更新。');
        }
        Log::info('Machine update process started.', ['machine_id' => $machine->id, 'request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Name is required for update
            // 'auth_key' => 'nullable|string|exists:machine_auth_keys,auth_key', // Auth Key - typically not updated directly here
            'chip_hardware_id' => [ // Allow updating hardware ID on the bound key
                'nullable',
                'string',
                'max:255',
                // Validate unique, ignoring the current machine's bound key
                Rule::unique('machine_auth_keys', 'chip_hardware_id')->ignore($machine->machineAuthKey->id ?? null)
            ],
            'machine_type' => ['required', 'string', Rule::in(['pinball', 'claw', 'points_redemption', 'ticket_redemption', 'normally', 'money_slot'])],
            'owner_id' => 'required|exists:users,id',
            'arcade_id' => 'required|exists:arcades,id',
            'credit_value' => 'nullable|numeric|min:0',
            'balls_per_credit' => 'nullable|integer|min:1',
            'points_per_credit_action' => 'nullable|integer|min:1',
            'payout_type' => ['nullable', 'string', Rule::in(['points', 'tickets', 'tokens', 'prize', 'none', 'money_slot'])],
            'payout_unit_value' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean', // Allow toggling active state
        ]);

        if ($validator->fails()) {
            Log::warning('MachinesController@update validation failed.', ['machine_id' => $machine->id, 'errors' => $validator->errors()->all()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validatedData = $validator->validated();
        Log::info('MachinesController@update validation passed.', ['machine_id' => $machine->id, 'validated_data' => $validatedData]);


        try {
            DB::transaction(function () use ($validatedData, $machine) {
                $machineData = [
                    'name' => $validatedData['name'],
                    'arcade_id' => $validatedData['arcade_id'],
                    'machine_type' => $validatedData['machine_type'],
                    'owner_id' => $validatedData['owner_id'],
                    'credit_value' => $validatedData['credit_value'] ?? null,
                    'balls_per_credit' => $validatedData['balls_per_credit'] ?? null,
                    'points_per_credit_action' => $validatedData['points_per_credit_action'] ?? null,
                    'payout_type' => $validatedData['payout_type'] ?? null,
                    'payout_unit_value' => $validatedData['payout_unit_value'] ?? null,
                ];

                // Handle is_active update if present in validated data
                if (array_key_exists('is_active', $validatedData)) {
                    $machineData['is_active'] = $validatedData['is_active'];
                }

                // Update chip_hardware_id on the associated MachineAuthKey if provided
                if ($machine->machineAuthKey && array_key_exists('chip_hardware_id', $validatedData)) {
                    // The unique validation for chip_hardware_id is handled by the validator
                    $machine->machineAuthKey->chip_hardware_id = $validatedData['chip_hardware_id'] ?? null; // Allow setting to null
                    $machine->machineAuthKey->save();
                    Log::info('Updated chip_hardware_id on associated MachineAuthKey.', ['auth_key_id' => $machine->machineAuthKey->id, 'new_hardware_id' => $machine->machineAuthKey->chip_hardware_id]);
                } elseif (!$machine->machineAuthKey && array_key_exists('chip_hardware_id', $validatedData) && $validatedData['chip_hardware_id'] !== null) {
                    // Handle case where machine was updated to have a hardware ID but no key was bound yet?
                    // Or perhaps this scenario indicates an issue. Log a warning.
                    Log::warning('Attempted to update chip_hardware_id on a machine with no bound key.', ['machine_id' => $machine->id, 'provided_hardware_id' => $validatedData['chip_hardware_id']]);
                    // Depending on business logic, you might throw an error here.
                }


                Log::info('Attempting to update machine model.', ['id' => $machine->id, 'data' => $machineData]);
                $machineUpdateResult = $machine->update($machineData);
                Log::info('Machine model update result:', ['success' => $machineUpdateResult]);

                if (!$machineUpdateResult) {
                    throw new \Exception('Failed to update machine model.');
                }
            });

            Log::info('Machine update transaction completed successfully.', ['machine_id' => $machine->id]);
            return redirect()->back()->with('success', __('msg.machine') . '「' . $machine->name . '」' . __('msg.update') . __('msg.success'));
        } catch (\Exception $e) {
            Log::error('Error updating machine: ' . $e->getMessage(), [
                'machine_id' => $machine->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle the active state of the specified machine.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(Request $request, $id)
    {
        // Authorization check might be needed here based on user role and machine ownership/arcade
        $machine = Machine::findOrFail($id);
        $machine->is_active = $request->input('is_active', 0); // Get is_active value, default to 0
        $machine->save();
        Log::info('Machine active status toggled.', ['machine_id' => $machine->id, 'is_active' => $machine->is_active]);
        return redirect()->back()->with('success', __('msg.machine_status_updated_successfully')); // Adjust success message
    }

    /**
     * Activate the specified machine.
     * (Likely redundant if toggleActive exists)
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(Machine $machine)
    {
        // This method might be redundant if toggleActive handles both activation and deactivation.
        // If it's specifically for activation, ensure proper authorization.
        Log::warning('Deprecated activate method called.', ['machine_id' => $machine->id]);
        $machine->update(['is_active' => true]);
        // Usually no redirect needed unless called from a specific flow
        // return redirect()->back()->with('success', 'Machine activated successfully.');
        return redirect()->back()->with('info', '機器已啟用 (透過舊方法)。');
    }

    /**
     * Remove the specified machine from storage (soft delete).
     *
     * @param  \App\Models\Machine  $machine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Machine $machine)
    {
        // Authorization check might be needed here
        Log::info('Attempting to delete machine.', ['machine_id' => $machine->id]);

        try {
            DB::transaction(function () use ($machine) {
                // Handle the associated MachineAuthKey before deleting the machine
                if ($machine->machineAuthKey) { // Assumes a machineAuthKey() relationship exists
                    Log::info('Unbinding MachineAuthKey during machine deletion.', ['machine_id' => $machine->id, 'auth_key_id' => $machine->machineAuthKey->id]);
                    // Reset the key's status and unbind it from the machine
                    $machine->machineAuthKey()->update(['status' => 'pending', 'machine_id' => null, 'chip_hardware_id' => null]); // Also clear hardware ID? Depends on logic.
                }

                // Soft delete the machine
                Log::info('Soft deleting machine.', ['machine_id' => $machine->id]);
                $machine->delete();
            });

            Log::info('Machine and associated AuthKey unbound successfully.', ['machine_id' => $machine->id]);
            return redirect()->route('admin.machines.index')->with('success', __('msg.machine_deleted_successfully')); // Corrected route name

        } catch (\Exception $e) {
            // Log detailed error
            Log::error('Error deleting machine: ' . $e->getMessage(), ['machine_id' => $machine->id, 'trace' => $e->getTraceAsString()]);
            return redirect()->route('admin.machines.index')->with('error', __('msg.error_deleting_machine') . ': ' . $e->getMessage()); // Corrected route name
        }
    }

    // Add printKeys method if needed, similar to how it worked for chips
    // public function printKeys(Request $request) { ... }

    public function showMqttDashboard()
    {
        $machines = Machine::with('machineAuthKey')->get();

        // 從 Redis 獲取每台機器的即時狀態和數據
        $machines->each(function ($machine) {
            if ($machine->machineAuthKey && $machine->machineAuthKey->chip_hardware_id) {
                $chipId = $machine->machineAuthKey->chip_hardware_id;
                $redisStatusKey = "machine_status:{$chipId}";
                $redisDataKey = "machine_data:{$chipId}"; // 數據鍵

                $status = Redis::get($redisStatusKey); // 從 Redis 獲取狀態
                $data = Redis::get($redisDataKey); // 從 Redis 獲取數據

                // 將狀態附加到機器物件上
                $machine->isOnline = ($status === 'online');

                // 如果機器離線，則不顯示數據
                if (!$machine->isOnline) {
                    $machine->data = null;
                } else {
                    // 如果數據存在，解析為 JSON
                    $machine->data = $data ? json_decode($data, true) : null;
                }
            } else {
                $machine->isOnline = false; // 如果沒有 chip_hardware_id，預設為離線
                $machine->data = null; // 沒有 chip_hardware_id 的機器也沒有數據
            }
        });

        return view('espmqtt', [
            'machines' => $machines,
            'reverb_app_key' => env('VITE_REVERB_APP_KEY'),
            'reverb_host' => env('VITE_REVERB_HOST'),
            'reverb_port' => env('VITE_REVERB_PORT'),
            'reverb_scheme' => env('VITE_REVERB_SCHEME')
        ]);
    }
}
