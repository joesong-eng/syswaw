<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;
use App\Models\Arcade;
use App\Models\User;
use App\Models\MachineAuthKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MachineMachinesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $queryMachines = Machine::query();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);
        if ($ownerId) {
            $arcadeIds = Arcade::where('owner_id', $ownerId)->pluck('id');
            $queryMachines->whereIn('arcade_id', $arcadeIds);
        } else {
            $queryMachines->whereRaw('1 = 0');
        }
        $queryMachines->with(['arcade', 'owner', 'machineAuthKey']);
        $machines = $queryMachines->orderBy('created_at', 'desc')->paginate(15);
        $arcades = $ownerId ? Arcade::where('owner_id', $ownerId)->get(['id', 'name', 'currency']) : collect(); // 選取 currency
        $potentialMachineOwners = User::orderBy('name')->get();

        $availableAuthKeys = $ownerId ? MachineAuthKey::where('owner_id', $ownerId)
            ->whereNull('machine_id')
            ->where('status', 'pending')
            ->get() : collect();
        return view('machine.machines.index', compact('machines', 'arcades', 'potentialMachineOwners', 'availableAuthKeys'));
    }

    public function toggleActive(Request $request, Machine $machine)
    {
        $user = Auth::user();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        if (!$ownerId) {
            Log::warning('toggleActive: 無法確定使用者的 ownerId。', ['user_id' => $user->id]);
            return redirect()->back()->with('error', __('auth.unauthorized_action'));
        }

        $arcade = $machine->arcade;
        if (!$arcade || $arcade->owner_id !== $ownerId) {
            Log::warning('toggleActive: 機台不屬於授權的遊藝場。', [
                'user_id' => $user->id,
                'machine_id' => $machine->id,
                'arcade_owner_id' => $arcade ? $arcade->owner_id : 'N/A',
                'expected_owner_id' => $ownerId,
            ]);
            return redirect()->back()->with('error', __('auth.unauthorized_action'));
        }

        $newActiveState = $request->validate(['is_active' => 'required|boolean'])['is_active'];
        $machine->is_active = $newActiveState;
        $machine->save();

        return redirect()->back()->with('success', __('msg.machine_status_updated_successfully'));
    }
    public function store(Request $request)
    {
        $allowedMachineCategories = array_keys(config('machines.templates', []));
        $validated = $request->validate([
            'auth_key' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'machine_category' => ['required', 'string', Rule::in($allowedMachineCategories)],
            'machine_type' => 'nullable|string|max:255',
            'arcade_id' => 'required|exists:arcades,id',
            'chip_hardware_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('machine_auth_keys')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
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
            'accepted_denominations.*' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();
            $authKey = MachineAuthKey::where('auth_key', $validated['auth_key'])->first();
            if ($authKey) {
                if ($authKey->machine_id !== null) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', '此金鑰已被其他機器綁定。')
                        ->withInput();
                }
                if (is_null($authKey->chip_hardware_id)) {
                    $authKey->chip_hardware_id = $validated['chip_hardware_id'];
                } elseif ($authKey->chip_hardware_id !== $validated['chip_hardware_id']) {
                    Log::warning('嘗試使用已綁定不同硬體 ID 的金鑰。', [
                        'auth_key' => $validated['auth_key'],
                    ]);
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', '金鑰存在但其硬體ID與提供的不符。')
                        ->withInput();
                }
                $authKey->status = 'active';
                $authKey->owner_id = $validated['owner_id'];
                $authKey->save();
            } else {
                $authKey = MachineAuthKey::create([
                    'auth_key' => $validated['auth_key'],
                    'chip_hardware_id' => $validated['chip_hardware_id'],
                    'owner_id' => $validated['owner_id'],
                    'created_by' => Auth::id(),
                    'status' => 'active',
                    'expires_at' => now()->addHours(24),
                ]);
            }

            $machine = Machine::create([
                'name' => $validated['name'],
                'machine_category' => $validated['machine_category'],
                'machine_type' => $validated['machine_type'] ?? null,
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'created_by' => Auth::id(),
                'auth_key_id' => $authKey->id,
                'status' => 'active',
                'is_active' => true,
                'coin_input_value'      => $validated['coin_input_value'] ?? null,
                'credit_button_value'   => $validated['credit_button_value'] ?? null,
                'payout_button_value'   => $validated['payout_button_value'] ?? null,
                'payout_type'           => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $validated['bill_acceptor_enabled'] ?? false,
                'bill_currency'         => $validated['bill_currency'] ?? null,
                'accepted_denominations' => $validated['accepted_denominations'] ?? null,
            ]);

            if ($machine->machine_category === 'money_slot') { // Logic updated to use machine_category
                $machine->bill_acceptor_enabled = true;
            }
            $authKey->machine_id = $machine->id;
            $authKey->save();

            DB::commit();

            return redirect()->route('machine.machines.index')
                ->with('success', __('msg.machine_added_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating machine: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()
                ->with('error', __('msg.error_creating_machine') . ': ' . $e->getMessage())
                ->withInput();
        }
    }
    public function update(Request $request, Machine $machine)
    {
        $user = Auth::user();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        if (!$ownerId || !$machine->arcade || $machine->arcade->owner_id !== $ownerId) {
            Log::warning('updateMachine: Unauthorized attempt.', [
                'user_id' => $user->id,
                'machine_id' => $machine->id,
                'arcade_owner_id' => $machine->arcade ? $machine->arcade->owner_id : 'N/A',
                'expected_owner_id' => $ownerId,
            ]);
            return redirect()->back()->with('error', __('msg.unauthorized_action'))->withInput();
        }
        Log::info('MachineMachinesController@update incoming request data:', $request->all());

        $allowedMachineCategories = array_keys(config('machines.templates', []));
        Log::info('MachineMachinesController@update allowed machine categories:', $allowedMachineCategories);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'machine_category' => ['required', 'string', Rule::in($allowedMachineCategories)],
            'machine_type' => 'nullable|string|max:255',
            'arcade_id' => 'required|exists:arcades,id',
            'auth_key' => 'nullable|string|exists:machine_auth_keys,auth_key',
            'chip_hardware_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('machine_auth_keys', 'chip_hardware_id')->ignore($machine->machineAuthKey->id ?? null, 'id')
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
            'accepted_denominations.*' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $machineData = [
                'name' => $validated['name'],
                'machine_category' => $validated['machine_category'],
                'machine_type' => $validated['machine_type'] ?? null,
                'arcade_id' => $validated['arcade_id'],
                'owner_id' => $validated['owner_id'],
                'coin_input_value' => $validated['coin_input_value'] ?? null,
                'credit_button_value' => $validated['credit_button_value'] ?? null,
                'payout_button_value' => $validated['payout_button_value'] ?? null,
                'payout_type' => $validated['payout_type'] ?? 'none',
                'payout_unit_value' => $validated['payout_unit_value'] ?? null,
                'revenue_split' => $validated['revenue_split'] ?? null,
                'bill_acceptor_enabled' => $validated['bill_acceptor_enabled'] ?? false,
                'bill_currency'         => $validated['bill_currency'] ?? null,
                'accepted_denominations' => $validated['accepted_denominations'] ?? null,
            ];

            if ($validated['machine_category'] === 'money_slot') {
                $machineData['bill_acceptor_enabled'] = true;
            }

            $currentAuthKey = $machine->machineAuthKey;
            $submittedAuthKeyValue = $validated['auth_key'] ?? null;
            $submittedChipHardwareIdValue = $validated['chip_hardware_id'] ?? null;
            $newAuthKeyProvided = !empty($submittedAuthKeyValue);

            if ($newAuthKeyProvided) {
                $newAuthKey = MachineAuthKey::where('auth_key', $submittedAuthKeyValue)->firstOrFail();

                if ($newAuthKey->machine_id !== null && $newAuthKey->machine_id != $machine->id) {
                    DB::rollBack();
                    return redirect()->back()->with('error', '提供的新金鑰已被其他機器綁定。')->withInput();
                }

                if ($currentAuthKey && $currentAuthKey->id !== $newAuthKey->id) {
                    $currentAuthKey->update(['status' => 'pending', 'machine_id' => null, 'chip_hardware_id' => null]);
                }

                $newAuthKeyUpdateData = [
                    'status' => 'active',
                    'machine_id' => $machine->id,
                    'owner_id' => $validated['owner_id'],
                ];
                if (array_key_exists('chip_hardware_id', $validated) && $submittedChipHardwareIdValue !== null) {
                    $existingKeyWithHwId = MachineAuthKey::where('chip_hardware_id', $submittedChipHardwareIdValue)
                        ->where('id', '!=', $newAuthKey->id)
                        ->first();
                    if ($existingKeyWithHwId) {
                        DB::rollBack();
                        return redirect()->back()->with('error', __('msg.chip_hardware_id_already_used'))->withInput();
                    }
                    $newAuthKeyUpdateData['chip_hardware_id'] = $submittedChipHardwareIdValue;
                } elseif (array_key_exists('chip_hardware_id', $validated) && $submittedChipHardwareIdValue === null) {
                    $newAuthKeyUpdateData['chip_hardware_id'] = null;
                }

                $newAuthKey->update($newAuthKeyUpdateData);
                $machineData['auth_key_id'] = $newAuthKey->id;
            } elseif ($currentAuthKey && array_key_exists('chip_hardware_id', $validated) && $currentAuthKey->chip_hardware_id !== $submittedChipHardwareIdValue) {
                if ($submittedChipHardwareIdValue !== null) {
                    $existingKeyWithHwId = MachineAuthKey::where('chip_hardware_id', $submittedChipHardwareIdValue)
                        ->where('id', '!=', $currentAuthKey->id)
                        ->first();
                    if ($existingKeyWithHwId) {
                        DB::rollBack();
                        return redirect()->back()->with('error', __('msg.chip_hardware_id_already_used_existing'))->withInput();
                    }
                }
                $currentAuthKey->update(['chip_hardware_id' => $submittedChipHardwareIdValue]);
            }
            if (!$newAuthKeyProvided && $currentAuthKey && $currentAuthKey->owner_id != $validated['owner_id']) {
                $currentAuthKey->update(['owner_id' => $validated['owner_id']]);
            }

            $machine->update($machineData);

            DB::commit();

            return redirect()->route('machine.machines.index')->with('success', __('msg.machine_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating machine in MachineMachinesController: ' . $e->getMessage(), ['machine_id' => $machine->id, 'request_data' => $request->all(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', __('msg.error_updating_machine') . ': ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Machine $machine)
    {
        $user = Auth::user();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        // 驗證使用者是否有權限刪除此機台
        if (!$ownerId || !$machine->arcade || $machine->arcade->owner_id !== $ownerId) {
            Log::warning('destroyMachine: Unauthorized attempt.', [
                'user_id' => $user->id,
                'machine_id' => $machine->id,
                'arcade_owner_id' => optional($machine->arcade)->owner_id ?? 'N/A',
                'expected_owner_id' => $ownerId,
            ]);
            return redirect()->back()->with('error', __('auth.unauthorized_action'));
        }

        try {
            DB::beginTransaction();

            if ($machine->machineAuthKey) {
                Log::info('Unbinding MachineAuthKey during machine deletion.', ['machine_id' => $machine->id, 'auth_key_id' => $machine->machineAuthKey->id]);
                $machine->machineAuthKey()->update(['status' => 'pending', 'machine_id' => null, 'chip_hardware_id' => null]);
            }

            Log::info('Soft deleting machine.', ['machine_id' => $machine->id]);
            $machine->delete();

            DB::commit();

            return redirect()->route('machine.machines.index')->with('success', __('msg.machine_deleted_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting machine in ArcadeMachinesController: ' . $e->getMessage(), ['machine_id' => $machine->id]);
            return redirect()->back()->with('error', __('msg.error_deleting_machine') . ': ' . $e->getMessage());
        }
    }
}
