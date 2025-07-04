<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Arcade;
use App\Models\ArcadeKey;
use App\Models\VsArcade; // 雖然沒用到，但先保留
use App\Models\Machine; // 雖然沒用到，但先保留
use App\Models\User;
use Illuminate\Http\Request;
// use App\Models\MachineDataRecord; // 引入 MachineDataRecord
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB; // 引入 DB facade
use Illuminate\Support\Facades\Hash; // 雖然沒用到，但先保留
use Illuminate\Support\Facades\Storage; // 引入 Storage facade
use Illuminate\Validation\Rule; // 引入 Rule facade
use Illuminate\Support\Facades\Log; // 引入 Log facade
use Illuminate\Support\Str; // 確保已引入 Str

class ArcadesController extends Controller
{

    public function arcadeKey()
    {
        $this->clear_expire_key();
        // 預加載 creator 和 arcade 關聯
        $arcadeKeys = ArcadeKey::with(['creator', 'arcade'])->get();
        return view('admin/arcade/arcadeKey', compact('arcadeKeys'));
    }

    private function clear_expire_key()
    {
        // 刪除未使用且過期的金鑰
        $deleted = ArcadeKey::where('used', false)
            ->where('expires_at', '<', now())
            ->delete();
        // 記錄一下操作結果
        \Log::info("已刪除 {$deleted} 個過期且未使用的金鑰");
    }

    public function keyDestroy(ArcadeKey $id)
    {
        $id->delete();
        return redirect()->route('admin.arcadeKey')->with('success', 'API token deleted successfully.');
    }
    public function keyStore(Request $request)
    {
        $token = $this->createArcadeKey();
        return redirect()->route('admin.arcadeKey')->with('success', 'API token created successfully.');
    }
    private function createArcadeKey(): string
    {
        $token = bin2hex(random_bytes(16)); // 產生隨機 API token
        ArcadeKey::create([
            'token' => $token,
            'expires_at' => now()->addHours(3),
            'authenticatable_type' => 'Arcade',
            'created_by' => Auth::id(), // 記錄當前用戶的 ID
        ]);
        return $token;
    }

    //**Arcades  */
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) { // 根據用戶角色篩選店鋪// 管理員可以查看所有店鋪
            $arcades = Arcade::all();
        } elseif ($user->hasRole('arcade-owner')) { // 店主只能查看自己擁有的店鋪
            $arcades = Arcade::where('owner_id', $user->id)->get();
        } elseif ($user->hasRole('arcade-staff')) { // 管理員查看其父用戶（店主）擁有的店鋪
            $arcades = Arcade::where('owner_id', $user->parent_id)->get();
        } else {
            $arcades = collect(); // 預設返回空集合或其他邏輯
        }
        // dd($arcades);
        // 找出所有role是arcade-owner
        $arcadeOwners = User::role('arcade-owner')->get();
        $machieOwners = User::role('machine-owner')->get();
        $arcadeKeys = ArcadeKey::with('creator')->get();
        if ($user->hasRole('admin')) {
            return view('admin.arcade.index', compact('arcades', 'arcadeKeys', 'arcadeOwners', 'machieOwners'));
        } elseif ($user->hasRole('arcade-owner') || $user->hasRole('arcade-staff')) {
            return view('arcade.index', compact('arcades', 'arcadeKeys'));
        } else {
            return redirect()->route('dashboard');
        }
    }
    public function destroy($id)
    {
        $arcade = Arcade::with('authorizeKey')->find($id);
        if (!$arcade) {
            return redirect()->back()->with('error', '商店不存在');
        }

        // --- 新增：權限檢查 ---
        $user = Auth::user();
        // 預設只允許 admin 或 arcade-owner 刪除自己的 Arcade
        if (!($user->hasRole('admin') || ($user->hasRole('arcade-owner') && $arcade->owner_id == $user->id))) {
            abort(403, 'Unauthorized action. Only admins or the owner can delete an arcade.');
        }
        // --- 權限檢查結束 ---

        try {
            DB::transaction(function () use ($arcade) {
                // 刪除關聯的 ArcadeKey（如果存在）
                if ($arcade->authorizeKey) {
                    \Log::info('Deleting ArcadeKey ID: ' . $arcade->authorizeKey->id);
                    $arcade->authorizeKey->delete();
                } else {
                    \Log::info('No authorize_key associated with Arcade ID: ' . $arcade->id);
                }

                // 刪除 Arcade
                \Log::info('Deleting Arcade ID: ' . $arcade->id);
                $arcade->delete();
            });

            return redirect()->back()->with('success', '商店已成功刪除');
        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', '刪除時發生錯誤：' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // 驗證邏輯根據是否已登入進行調整
        $rules = [
            'arcade_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'arcadeKey' => 'required|string|exists:arcade_keys,token',
            'currency' => 'required|string|max:3', // 新增 currency 驗證
            'type' => 'required|string|in:physical,virtual', // 添加 type 驗證規則
        ];

        if (!Auth::check()) {
            $rules = array_merge($rules, [ // 未登入時需要提供用戶資訊
                'user_name' => 'required|string|max:255',
                'new_user_email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        $validatedData = $request->validate($rules);

        // 確保 'arcade-owner' 角色存在
        $storeOwnerRole = Role::firstOrCreate(['name' => 'arcade-owner', 'guard_name' => 'web']);

        // 驗證金鑰是否可用
        $apiKey = ArcadeKey::where('token', $validatedData['arcadeKey'])
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$apiKey) {
            return redirect()->back()->withErrors(['arcadeKey' => '無效或已使用的金鑰']);
        }

        // 創建遊藝場和擁有者
        $result = $this->createStoreAndOwner($validatedData);

        if ($result['success']) {
            $apiKey->update(['used' => true]); // 標記金鑰為已使用

            if (!Auth::check()) { // 若用戶未登入，則自動登入
                $user = User::where('email', $validatedData['new_user_email'])->first();
                if ($user) {
                    Auth::login($user);
                    return redirect()->route('dashboard')->with('success', '商鋪和帳戶創建成功，並已自動登入！');
                }
            }

            $role = Auth::user()->roles->first()->name;
            if ($role == 'admin') {
                return redirect()->route('admin.arcades.index')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            } elseif ($role == 'arcade-owner' || $role == 'arcade-staff') {
                return redirect()->route('arcade.index')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            } else {
                return redirect()->route('dashboard')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            }
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function update(Request $request, $id)
    {
        $arcade = Arcade::findOrFail($id);

        // 確認 $arcade 是否正確綁定
        if (!$arcade->exists) {
            return redirect()->back()->with('error', '找不到該店鋪！');
        }

        // 權限檢查
        $user = Auth::user();
        if (!($user->hasRole('admin') ||
            ($user->hasRole('arcade-owner') && $arcade->owner_id == $user->id) ||
            ($user->hasRole('arcade-staff') && $arcade->owner_id == $user->parent_id))) {
            abort(403, 'Unauthorized action.');
        }

        // 驗證請求數據
        $validationRules = [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'business_hours' => 'nullable|string|max:255', // 新增 business_hours 驗證
            'currency' => 'required|string|max:3', // 新增 currency 驗證
            'authorization_code' => ['nullable', 'string', 'max:255', Rule::unique('arcades')->ignore($arcade->id)], // 新增授權碼驗證
            'image_url' => 'nullable|string', // 前端傳來的圖片路徑
        ];

        if ($user->hasRole('admin')) {
            $validationRules['owner_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($validationRules);

        // 更新基本資訊
        $arcade->name = $validated['name'];
        $arcade->address = $validated['address'];
        $arcade->phone = $validated['phone'];
        $arcade->currency = $validated['currency']; // 更新 currency
        $arcade->business_hours = $validated['business_hours'] ?? "24"; // 新增 business_hours 更新
        $arcade->authorization_code = $validated['authorization_code'] ?? null; // 更新授權碼

        // 處理圖片路徑
        if ($request->has('image_url')) { // 檢查 image_url 是否在請求中，即使是 null 或空字串
            $newImageUrl = $request->input('image_url');
            if ($arcade->image_url && $arcade->image_url !== $newImageUrl && $arcade->image_url !== 'default-store.jpg') {
                // 刪除舊的非預設圖片
                if (Storage::disk('public')->exists('images/' . $arcade->image_url)) {
                    Storage::disk('public')->delete('images/' . $arcade->image_url);
                }
            }
            // 如果新圖片是 'default-store.jpg'，則將資料庫中的 image_url 設為 null 或 'default-store.jpg'
            $arcade->image_url = ($newImageUrl === 'default-store.jpg') ? null : $newImageUrl;
        }

        // 只有 admin 可以修改 owner_id
        if ($user->hasRole('admin') && isset($validated['owner_id'])) {
            $arcade->owner_id = $validated['owner_id'];
        }

        // 保存更新
        $arcade->save();

        // 記錄日誌
        Log::info('Arcade updated: ' . $arcade->id, [
            'name' => $arcade->name,
            'image_url' => $arcade->image_url,
        ]);

        // 根據用戶角色決定重定向路徑
        $redirectRoute = $user->hasRole('admin') ? 'admin.arcades' : 'arcade.index';
        return redirect()->back()->with('success', __('msg.arcade_info') . __('msg.update') . __('msg.success'));
    }

    private function createStoreAndOwner(array $validatedData)
    {
        DB::beginTransaction(); // 開啟交易
        try {
            // 檢查是否有登入的使用者
            if (Auth::check()) {
                $user = Auth::user(); // 已登入的使用者
            } else {
                $user = User::create([ // 未登入，創建店主用戶
                    'name' => $validatedData['user_name'],
                    'email' => $validatedData['new_user_email'],
                    'password' => bcrypt($validatedData['password']),
                ]);
                // 分配 'arcade-owner' 角色給用戶
                $user->assignRole('arcade-owner');
            }

            $arcadeKeyId = ArcadeKey::where('token', $validatedData['arcadeKey'])->value('id');

            $arcade = Arcade::create([
                'name'      => $validatedData['arcade_name'],
                'owner_id'  => $user->id,
                'manager'   => $user->id,
                'address'   => $validatedData['address'] ?? null,
                'currency'  => $validatedData['currency'], // 保存 currency
                'phone'     => $validatedData['phone'] ?? null,
                'type'      => $validatedData['type'], // 保存 type 字段
                'is_active' => false,
                'authorize_key' => $arcadeKeyId,
            ]);
            // dd($arcade->toArray());

            DB::commit(); // 提交交易
            return ['success' => true, 'message' => '店主用戶和商鋪已成功創建！'];
        } catch (\Exception $e) {
            DB::rollBack(); // 回滾交易
            return ['success' => false, 'message' => '創建過程中出現錯誤：' . $e->getMessage()];
        }
    }
    public function create()
    { // 查找金鑰，檢查是否過期且是否未使用
        $arcadeKey = $this->createArcadeKey();
        if (strlen($arcadeKey) !== 32) {
            return redirect()->route('dashboard')->with('error', '無效或過期的金鑰');
        }
        $apiKey = ArcadeKey::where('token', $arcadeKey)
            ->where('expires_at', '>', now())  // 確保金鑰尚未過期
            ->where('used', false)->first();  // 確保金鑰未被使用
        if (!$apiKey) return redirect('/')->with('error', '無效或過期的金鑰');
        // 金鑰有效，將金鑰標記為已使用
        return view('arcade.create', ['arcadeKey' => $arcadeKey]);
    }

    public function bindArcade($arcadeKey)
    {
        $vaild = ArcadeKey::where('token', $arcadeKey)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();
        if (!$vaild) {
            return redirect('/')->with('error', '無效或過期的金鑰');
        }
        return view('arcade.create', compact('arcadeKey'));
    }


    private function orgUpdate(Request $request, Arcade $arcade)
    {
        // 驗證請求數據
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:255',
            'owner_id'  => 'required|exists:users,id',
            'authorization_code' => ['nullable', 'string', 'max:255', Rule::unique('arcades')->ignore($arcade->id)], // 新增授權碼驗證
            'image_url' => 'nullable|string', // 前端傳來的圖片路徑
        ]);

        // 更新基本資訊
        $arcade->name = $request->input('name');
        $arcade->address = $request->input('address');
        $arcade->phone = $request->input('phone');
        $arcade->owner_id = $request->input('owner_id');
        $arcade->authorization_code = $request->input('authorization_code'); // 新增授權碼更新

        // 處理圖片路徑
        if ($request->filled('image_url')) {
            if ($arcade->image_url && $arcade->image_url !== $request->image_url) {
                // 刪除舊圖片（如果存在）
                if (Storage::disk('public')->exists('images/' . $arcade->image_url)) {
                    Storage::disk('public')->delete('images/' . $arcade->image_url);
                }
            }
            $arcade->image_url = $request->input('image_url');
        }

        // 保存更新
        $arcade->save();

        // 記錄日誌
        Log::info('Arcade updated (via orgUpdate): ' . $arcade->id, [
            'name' => $arcade->name,
            'image_url' => $arcade->image_url,
        ]);
    }

    public function upload(Request $request)
    {
        Log::info('Image upload request received.');

        if (!$request->hasFile('image')) {
            Log::error('No image file found in request.');
            return response()->json(['success' => false, 'message' => '未找到圖片檔案。'], 400);
        }

        $file = $request->file('image');
        Log::info('File details: ', ['original_name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

        // 驗證檔案
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 示例驗證規則
        ]);

        if ($validator->fails()) {
            Log::error('Image validation failed: ', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => '圖片驗證失敗: ' . $validator->errors()->first()], 422);
        }

        try {
            $filename = time() . '.' . $file->getClientOriginalExtension();
            // 確保 'images' 資料夾在 storage/app/public/ 下存在
            // 並且您已經執行了 php artisan storage:link
            $path = $file->storeAs('images', $filename, 'public'); // 儲存到 public disk 的 images 資料夾

            Log::info('Image stored successfully: ' . $path);

            return response()->json([
                'success' => true,
                'message' => '圖片上傳成功！',
                'image_path' => $filename, // 只返回檔案名稱
                'url' => Storage::disk('public')->url($path) // 返回完整的 URL
            ]);
        } catch (\Exception $e) {
            Log::error('Image upload exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => '圖片上傳時發生錯誤: ' . $e->getMessage()], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        $arcade = Arcade::findOrFail($id);

        // --- 新增：權限檢查 ---
        $user = Auth::user();
        if (!($user->hasRole('admin') || ($user->hasRole('arcade-owner') && $arcade->owner_id == $user->id) || ($user->hasRole('arcade-staff') && $arcade->owner_id == $user->parent_id))) {
            abort(403, 'Unauthorized action.');
        }
        // --- 權限檢查結束 ---

        $arcade->is_active = $request->input('is_active', 0);
        $arcade->save();
        return redirect()->back()->with('success', '商店狀態已更新');
    }

    // 移除員工管理相關方法
    // public function staff() ...
    // public function destroyManager(User $user) ...
    // public function updateManager(Request $request, User $user) ...


    public function destroyVsStore($id)
    { // 查找要刪除的虛擬商店
        $vsStore = VsStore::findOrFail($id); // 檢查用戶是否有權刪除此商店（可選步驟）
        if (Auth::id() !== $vsStore->created_by && !Auth::user()->isAdmin()) {
            return redirect()->back()->withErrors('您無權刪除此虛擬商店。');
        }

        // 刪除商店
        $vsStore->delete();

        return redirect()->route('machines.visualStore')->with('success', '虛擬商店刪除成功');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Adjust validation rules as needed
        ]);

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                // Generate a unique name for the image, or use the original name if preferred
                $imageName = time() . '.' . $image->getClientOriginalExtension();

                // Store the image in the 'public/images' directory.
                // The 'public' disk usually maps to 'storage/app/public'.
                // The storeAs method returns the path relative to the disk's root.
                $path = $image->storeAs('images', $imageName, 'public');

                // The JavaScript expects 'image_path' to be just the filename.
                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully.',
                    'image_path' => $imageName, // Return just the filename
                    // 'full_storage_path' => $path, // For debugging or other uses
                    // 'public_url' => Storage::url($path) // Full public URL
                ]);
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage()); // Optional: Log the error
                return response()->json([
                    'success' => false,
                    'message' => 'Image upload failed: ' . $e->getMessage(),
                ], 500); // Internal Server Error
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided.',
        ], 400); // Bad Request
    }


    // ... (其他方法) ...

    public function regenerateAuthCode(Request $request, Arcade $arcade) // 使用路由模型綁定
    {
        // 權限檢查 (確保只有遊藝場擁有者或管理員可以操作)
        $user = Auth::user();
        if (!($user->hasRole('admin') || ($user->hasRole('arcade-owner') && $arcade->owner_id == $user->id))) {
            return response()->json(['success' => false, 'message' => __('auth.unauthorized')], 403);
        }

        try {
            $newAuthCode = Str::random(12); // 生成新的12位隨機碼
            $arcade->authorization_code = $newAuthCode;
            $arcade->save();

            Log::info('Authorization code regenerated for Arcade ID: ' . $arcade->id . '. New code: ' . $newAuthCode);

            return response()->json([
                'success' => true,
                'authorization_code' => $newAuthCode,
                'message' => __('msg.auth_code_regenerated_successfully') ?? '授權碼已成功重新生成！'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to regenerate authorization code for Arcade ID: ' . $arcade->id . '. Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('msg.failed_to_regenerate_auth_code') ?? '重新生成授權碼失敗，請稍後再試。'
            ], 500);
        }
    }
}
