<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineAuthKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MachineMachineKeyController extends Controller
{
    /**
     * Display a listing of the machine authentication keys relevant to the machine owner/staff.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MachineAuthKey::query();

        // Determine the owner ID to filter by (either self or parent for staff)
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        if ($ownerId) {
            $query->where('owner_id', $ownerId);

            // Add filtering based on status (e.g., active, pending, inactive)
            $filter = $request->input('filter', 'all');
            if (in_array($filter, ['active', 'pending', 'inactive'])) {
                $query->where('status', $filter);
            }
        } else {
            // Fallback for unexpected roles, should be handled by middleware
            $query->whereRaw('1 = 0');
        }

        // Eager load relationships
        $query->with(['creator', 'owner', 'machine']);

        $machineAuthKeys = $query->orderBy('created_at', 'desc')->paginate(15);

        // Ensure the view path exists: resources/views/machine/auth_keys/index.blade.php
        return view('machine.auth_keys.index', compact('machineAuthKeys', 'filter'));
    }

    /**
     * Store newly created machine authentication keys in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', Rule::in([1, 10, 20, 30, 50])],
            // 'owner_id' is implicitly the current machine-owner
        ]);

        $quantity = $validated['quantity'];
        $user = Auth::user();
        $ownerId = $user->id; // Keys are owned by the machine-owner creating them

        $generatedCount = 0;

        for ($i = 0; $i < $quantity; $i++) {
            try {
                MachineAuthKey::create([
                    'auth_key' => Str::random(8), // 8-character key
                    'owner_id' => $ownerId,
                    'created_by' => $user->id,
                    'status' => 'pending',
                    'expires_at' => now()->addHours(24), // Default 24-hour expiration
                ]);
                $generatedCount++;
            } catch (\Exception $e) {
                Log::error("Error generating MachineAuthKey for machine-owner: " . $e->getMessage(), ['user_id' => $user->id]);
            }
        }

        return redirect()->route('machine.auth_keys.index')
            ->with('success', __('msg.auth_keys_generated_successfully', ['count' => $generatedCount]));
    }

    /**
     * Generate a single machine authentication key and return it as JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSingleKey(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->id; // Key owned by the machine-owner

        try {
            $authKeyString = Str::random(8);

            $newKey = MachineAuthKey::create([
                'auth_key' => $authKeyString,
                'owner_id' => $ownerId,
                'created_by' => $user->id,
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            return response()->json(['success' => true, 'auth_key' => $newKey->auth_key, 'message' => __('msg.auth_key_generated_successfully_single')]);
        } catch (\Exception $e) {
            Log::error("Error generating single MachineAuthKey for machine-owner: " . $e->getMessage(), ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => __('msg.error_generating_auth_key') . ': ' . $e->getMessage()], 500);
        }
    }

    /**
     * Prepare data for printing selected keys.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function printKeys(Request $request)
    {
        $ids = $request->input('selected_ids');
        if (empty($ids)) {
            return redirect()->route('machine.auth_keys.index')->with('error', __('msg.select_keys_to_print'));
        }

        $user = Auth::user();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        $keysToPrint = MachineAuthKey::whereIn('id', $ids)->where('owner_id', $ownerId)->get();

        if ($keysToPrint->isEmpty()) {
            return redirect()->route('machine.auth_keys.index')->with('error', __('msg.selected_keys_not_found_or_not_owned'));
        }

        // Ensure the view path exists: resources/views/machine/auth_keys/print_keys.blade.php
        // This view can be a copy of arcade.auth_keys.print_keys or admin.machine_auth_keys.print_keys
        return view('machine.auth_keys.print_keys', ['chipKeys' => $keysToPrint]);
    }

    /**
     * Remove the specified machine authentication key from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $ownerId = $user->hasRole('machine-owner') ? $user->id : ($user->hasRole('machine-staff') ? $user->parent_id : null);

        $key = MachineAuthKey::where('id', $id)->where('owner_id', $ownerId)->firstOrFail();

        if ($key->machine_id !== null) {
            return redirect()->route('machine.auth_keys.index')->with('error', __('msg.auth_key_bound_cannot_delete'));
        }
        $key->delete();
        return redirect()->route('machine.auth_keys.index')->with('success', __('msg.auth_key_deleted_successfully'));
    }
}
