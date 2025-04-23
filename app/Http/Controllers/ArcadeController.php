<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Arcade;
use App\Models\ArcadeKey;
use App\Models\VsArcade;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB; // 引入 DB facade
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // 引入 Storage facade
use Illuminate\Support\Facades\Log; // 引入 Log facade

/** @var \Illuminate\Contracts\Auth\Authenticatable $user */
class ArcadeController extends Controller
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
        logger()->info("已刪除 {$deleted} 個過期且未使用的金鑰");
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

        if ($user->hasRole('admin')) { // 根據用戶角色篩選店鋪
            // 管理員可以查看所有店鋪
            $arcades = Arcade::all();
        } elseif ($user->hasRole('arcade-owner')) {
            // 店主只能查看自己擁有的店鋪
            $arcades = Arcade::where('owner_id', $user->id)->get();
        } elseif ($user->hasRole('arcade-manager')) {
            // 管理員查看其父用戶（店主）擁有的店鋪
            $arcades = Arcade::where('owner_id', $user->parent_id)->get();
        } else {
            // 預設返回空集合或其他邏輯
            $arcades = collect();
        }
        // 找出所有role是arcade-owner
        $arcadeOwners = User::role('arcade-owner')->get();
        $arcadeKeys = ArcadeKey::with('creator')->get();
        if ($user->hasRole('admin')) {
            return view('admin.arcade.index', compact('arcades', 'arcadeKeys', 'arcadeOwners'));
        } elseif ($user->hasRole('arcade-owner') || $user->hasRole('arcade-manager')) {
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
                return redirect()->route('admin.arcades')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            } elseif ($role == 'arcade-owner' || $role == 'arcade-manager') {
                return redirect()->route('arcades.index')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            } else {
                return redirect()->route('dashboard')->with('success', '商鋪創建成功並已綁定當前帳戶！');
            }
        }

        return redirect()->back()->with('error', $result['message']);
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
    // public function bindArcade($arcadeKey){
    //     dd($arcadeKey);
    //     $arcadeKey = $arcadeKey ?? null;  // 若沒有值，就給 null （或其他預設值）
    //     return view('arcade.create', compact('arcadeKey'));
    // }
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
    public function update(Request $request, $id)
    {
        $arcade = Arcade::findOrFail($id);
        // 確認 $arcade 是否正確綁定
        if (!$arcade->exists) {
            return redirect()->back()->with('error', '找不到該店鋪！');
        }
        $this->orgUpdate($request, $arcade);
        return redirect()->back()->with('success', '店鋪更新成功！');
    }

    private function orgUpdate(Request $request, Arcade $arcade)
    {
        // 驗證請求數據
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:255',
            'owner_id'  => 'required|exists:users,id',
            'image_url' => 'nullable|string', // 前端傳來的圖片路徑
        ]);

        // 更新基本資訊
        $arcade->name = $request->input('name');
        $arcade->address = $request->input('address');
        $arcade->phone = $request->input('phone');
        $arcade->owner_id = $request->input('owner_id');

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
        Log::info('Arcade updated: ' . $arcade->id, [
            'name' => $arcade->name,
            'image_url' => $arcade->image_url,
        ]);
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $imageName, 'public');

                return response()->json([
                    'success' => true,
                    'message' => '圖片上傳成功！',
                    'image_path' => $imageName
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => '沒有上傳圖片！'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '上傳失敗：' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        $arcade = Arcade::findOrFail($id);
        $arcade->is_active = $request->input('is_active', 0);
        $arcade->save();
        return redirect()->back()->with('success', '商店狀態已更新');
    }

    //.machine get('machineOwners)

    // public function arcadeUpdate(Request $request, Arcade $arcade){
    //     $this->orgupdate($request,$store);
    //     return redirect()->route('stores')->with('success', '店鋪更新成功！');
    // }





    /**
     * manager相關員工
     */
    public function staff()
    {
        $staffs = User::find(Auth::id())->children()->with('roles')->get();
        return view('arcade.staff', compact('staffs'));
    }
    // public function addManager(Request $request){
    //     // 驗證輸入數據
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|string|min:8',
    //     ]);

    //     // 創建新管理員
    //     $manager = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //         'role_id' => Role::where('name', 'arcade-manager')->first()->id,
    //         'parent_id' => Auth::id(), // 這樣設置為當前用戶（machine-owner）
    //     ]);
    //             // 指定角色
    //     $manager->assignRole('arcade-manager');
    //     return redirect()->route('arcades.manager')->with('success', 'Store Manager created successfully!');
    // }
    public function destroyManager(User $user)
    {
        // 確保該用戶是當前登錄的 machine-owner 的管理員
        $this->authorize('delete', $user);  // 可自定義權限策略來確保刪除的是與當前 machine-owner 相關的管理員

        // 刪除該管理員
        $user->delete();

        return redirect()->route('stores.manager')->with('success', 'Manager deleted successfully!');
    }

    public function updateManager(Request $request, User $user)
    {
        $validated = $request->validate([ // 驗證輸入數據
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',  // 密碼是可選的
        ]);

        // 更新管理員資料
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        return redirect()->route('stores.manager')->with('success', 'Manager updated successfully!');
    }


    public function destroyVsStore($id)
    { // 查找要刪除的虛擬商店
        $vsStore = VsStore::findOrFail($id); // 檢查用戶是否有權刪除此商店（可選步驟）
        if (Auth::id() !== $vsStore->created_by && !Auth::user()->isAdmin()) {
            return redirect()->back()->withErrors('您無權刪除此虛擬商店。');
        }

        // 刪除商店
        $vsStore->delete();

        return redirect()->route('machine.visualStore')->with('success', '虛擬商店刪除成功');
    }
}
