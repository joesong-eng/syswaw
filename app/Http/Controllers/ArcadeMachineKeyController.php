<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineAuthKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // 引入 Str facade
use Illuminate\Validation\Rule; // 引入 Rule facade
use Illuminate\Validation\ValidationException; // 引入 ValidationException

class ArcadeMachineKeyController extends Controller
{
    /**
     * Display a listing of the machine authentication keys relevant to the arcade owner/staff.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MachineAuthKey::query();
        $ownerId = $user->hasRole('arcade-owner') ? $user->id : ($user->hasRole('arcade-staff') ? $user->parent_id : null);
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
            $filter = $request->input('filter', 'all');
            if (in_array($filter, ['active', 'pending', 'inactive'])) { // Assuming these are the statuses in MachineAuthKey
                $query->where('status', $filter);
            }
        } else {
            $query->whereRaw('1 = 0');
        }
        $query->with(['creator', 'owner', 'machine']); // Load creator, owner, and potentially bound machine
        $machineAuthKeys = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('arcade.auth_keys.index', compact('machineAuthKeys', 'filter')); // Assuming view path
    }

    /**
     * Store a newly created machine authentication key in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 修改：驗證輸入，確保 quantity 在允許的範圍內
        $validated = $request->validate([
            'quantity' => ['required', 'integer', Rule::in([1, 10, 20, 30, 50])],
            // 如果開放選擇擁有者，需要加入 owner_id 的驗證
            // 'owner_id' => 'required|integer|exists:users,id',
        ]);

        $quantity = $validated['quantity'];
        $user = Auth::user();
        // 如果開放選擇擁有者，從請求獲取 owner_id，否則預設為當前用戶
        // $ownerId = $request->input('owner_id', $user->id);
        $ownerId = $user->hasRole('arcade-owner') ? $user->id : ($user->hasRole('arcade-staff') ? $user->parent_id : null);

        $generatedCount = 0;

        for ($i = 0; $i < $quantity; $i++) {
            try {
                // 生成 8 位隨機金鑰，與 Admin 一致
                $authKey = Str::random(8);

                MachineAuthKey::create([
                    'auth_key' => $authKey,
                    'owner_id' => $ownerId, // 使用指定的 ownerId
                    'created_by' => $user->id, // Creator is the current user
                    'status' => 'pending', // Default status
                    'expires_at' => now()->addHours(24), // Default expiration
                ]);
                $generatedCount++;
            } catch (\Exception $e) {
                // 如果生成過程中出錯（例如金鑰重複，雖然機率低），記錄錯誤並繼續
                Log::error("Error generating MachineAuthKey: " . $e->getMessage());
                // 可以考慮在這裡停止或通知用戶部分成功
            }
        }

        return redirect()->route('arcade.auth_keys.index')->with('success', __('msg.auth_keys_generated_successfully', ['count' => $generatedCount])); // 返回成功訊息，包含數量
    }

    /**
     * Generate a single machine authentication key and return it as JSON.
     * This is typically called via AJAX from a modal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateSingleKey(Request $request)
    {
        $user = Auth::user();
        // Arcade owner should be the one generating this key for themselves.
        $ownerId = $user->id;
        try {
            // Generate an 8-character random key, similar to Admin\MachineAuthKeyController
            $authKeyString = Str::random(8);

            $newKey = MachineAuthKey::create([
                'auth_key' => $authKeyString,
                'owner_id' => $ownerId,
                'created_by' => $user->id,
                'status' => 'pending', // Default status for a newly generated key
                'expires_at' => now()->addHours(24), // Default expiration
            ]);

            return response()->json(['success' => true, 'auth_key' => $newKey->auth_key, 'message' => '金鑰生成成功！']);
        } catch (\Exception $e) {
            Log::error("Error generating single MachineAuthKey for arcade owner: " . $e->getMessage(), ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => '生成金鑰時發生錯誤: ' . $e->getMessage()], 500);
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
            return redirect()->route('arcade.auth_keys.index')->with('error', '請先選擇要列印的金鑰。');
        }

        $user = Auth::user();
        $ownerId = $user->hasRole('arcade-owner') ? $user->id : $user->parent_id;

        // 只查找屬於該 Owner 的金鑰
        $keysToPrint = MachineAuthKey::whereIn('id', $ids)->where('owner_id', $ownerId)->get();

        if ($keysToPrint->isEmpty()) {
            return redirect()->route('arcade.auth_keys.index')->with('error', '選擇的金鑰不存在或不屬於您。');
        }

        return view('arcade.auth_keys.print_keys', ['chipKeys' => $keysToPrint]); // 注意變數名稱是 chipKeys 以匹配視圖
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
        $ownerId = $user->hasRole('arcade-owner') ? $user->id : $user->parent_id;

        $key = MachineAuthKey::where('id', $id)->where('owner_id', $ownerId)->firstOrFail();
        // 修改：只檢查金鑰是否已綁定機器
        if ($key->machine_id !== null) {
            // 使用更接近用戶回報的錯誤訊息
            return redirect()->route('arcade.auth_keys.index')->with('error', '此金鑰已綁定機器，無法直接刪除。');
        }
        $key->delete();
        return redirect()->route('arcade.auth_keys.index')->with('success', '金鑰刪除成功。');
    }
}
