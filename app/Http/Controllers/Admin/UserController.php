<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; // 確保正確引用基礎控制器
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log; // 引入 Log facade
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller{
    public function index(){
        $users = User::with('roles')->get(); // 使用 eager loading 加載用戶角色
        $roles = Role::all(); // 獲取所有角色
        return view('users.index', compact('users', 'roles'));
    }
    public function create(){
        $roles = Role::all(); // 獲取所有角色
        return view('users.create', compact('roles'));
    }
   
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'parent_id' => 'nullable|exists:users,id',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => bcrypt($validated['password']),
        ]);
            // 分配角色
        $user->assignRole($validated['role']);
        if (isset($validated['parent_id'])) {// 檢查是否需要設置 parent_id
            $user->parent_id = $validated['parent_id'];
        }
        if ($user->save()) {// 保存用戶
            return redirect()->back()->with('success', '用戶創建成功！');
        } else {
            return redirect()->back()->with('error', '用戶創建失敗！');
        }
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name',
            'parent_id' => 'nullable|exists:users,id',
            'sidebar_permissions' => 'nullable|array', // 新增：接收側邊欄權限
        ]);
    
        // 更新用戶基本信息
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'sidebar_permissions' => $request->input('sidebar_permissions'), // 新增：儲存側邊欄權限
        ]);
    
        // 檢查是否需要更新 parent_id
        if (isset($validated['parent_id']) && $validated['parent_id'] !== $user->parent_id) {
            // 這裡可以添加額外的檢查或處理
            $user->parent_id = $validated['parent_id'];
        }
    
        // 分配角色
        $user->syncRoles([$validated['role']]);
    
        // 保存用戶
        if ($user->save()) {
            Log::info('User updated: ' . $user->id, [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
                'parent_id' => $user->parent_id,
                'sidebar_permissions' => $user->sidebar_permissions, // 新增：記錄側邊欄權限
            ]);
            return redirect()->back()->with('success', '用戶更新成功！');
        } else {
            return redirect()->back()->with('error', '用戶更新失敗！');
        }
    }
    public function destroy(User $user){
        $res = $user->delete();
        if ($res) {
            // 用戶刪除成功
            return redirect()->back()->with('success', '用戶刪除成功！');
        } else {
            // 用戶刪除失敗
            return redirect()->back()->with('error', '用戶刪除失敗！');
        }
    }
    // 切換用戶的啟用狀態
    public function deactivate(Request $request, User $user)
    {
        $isActive = $request->input('is_active');
        $user->is_active = $isActive;
        $user->save();
        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function search(Request $request)
    {
        // 確保用戶已登入
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        // 檢查是否為管理員
        if (!$user->hasRole('admin')) {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        // 從請求中獲取查詢文字和新用戶的角色名稱
        $query = $request->input('query', '');
        $selectedRoleName = $request->input('role', '');

        // 如果未提供角色名稱，返回空結果
        if (empty($selectedRoleName)) {
            return response()->json(['success' => true, 'users' => []]);
        }

        // 查找所選角色的 level
        $selectedRole = Role::where('name', $selectedRoleName)->first();
        if (!$selectedRole) {
            return response()->json(['success' => false, 'error' => 'Invalid role selected'], 400);
        }
        $selectedRoleLevel = $selectedRole->level;

        try {
            // 篩選層級高於所選角色的用戶
            $filteredUsers = User::whereHas('roles', function ($queryBuilder) use ($selectedRoleLevel) {
                    $queryBuilder->where('level', '<', $selectedRoleLevel);
                })
                ->where('name', 'like', "%{$query}%")
                ->get(['id', 'name']);

            return response()->json(['success' => true, 'users' => $filteredUsers]);
        } catch (\Exception $e) {
            \Log::error('Search users failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    public function verifyEmail(User $user){
        $user->update(['email_verified_at' => Carbon::now()]);
        return redirect()->back()->with('success', '用戶 Email 已成功驗證！');
    }

}