<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @method bool hasRole($roles)
 * @method bool hasAnyRole($roles)
 * @method bool hasAllRoles($roles)
 * @method \Illuminate\Database\Eloquent\Model assignRole(...$roles)
 * @method \Illuminate\Database\Eloquent\Model removeRole(...$roles)
 * @method \Illuminate\Database\Eloquent\Model syncRoles(...$roles)
 */
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get(); // 使用 eager loading 加載用戶角色
        $roles = Role::all(); // 獲取所有角色
        return view('users.index', compact('users', 'roles'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'filter_user_id' => 'nullable|exists:users,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // 分配角色
        $user->assignRole($validated['role']);

        // 可選：如果有 filter_user_id，可以用來關聯其他邏輯（例如父用戶）
        if ($request->filled('filter_user_id')) {
            // 示例：將 filter_user_id 設為新用戶的 parent_id（根據你的業務邏輯調整）
            $user->parent_id = $validated['filter_user_id'];
            $user->save();
        }
        return redirect()->back()->with('success', '用戶創建成功！');
    }
    public function update(Request $request, User $user)
    {
        $request->validate([ // 驗證表單數據
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name', // 確保角色存在
        ]);
        $user->name = $request->name; // 更新用戶信息
        $user->email = $request->email;
        if ($request->filled('password')) { // 如果需要更新密碼
            $request->validate(['password' => 'string|min:8']);
            $user->password = Hash::make($request->password);
        }
        $user->save();
        $user->syncRoles($request->role); // 更新角色
        return redirect()->route('users.index')->with('success', '用戶更新成功'); // 返回成功訊息或重新導向
    }
    // public function update(Request $request){
    //     $user = auth()->user(); // 取得當前登入用戶
    //     $request->validate([// 驗證表單數據
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
    //         'password' => 'nullable|string|min:8|confirmed', // 密碼可選
    //     ]);
    //     $user->name = $request->name;// 更新用戶資料
    //     $user->email = $request->email;
    //     if ($request->filled('password')) {
    //         $user->password = Hash::make($request->password); // 更新密碼
    //     }
    //     $user->save();
    //     return redirect()->route('user.edit')->with('success', '帳號資料更新成功');
    // }
    public function logs()
    {
        $logs = auth()->user()->logs()->latest()->get(); // 假設用戶有一個 `logs` 關聯
        return view('user.logs', compact('logs'));
    }
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', '用戶刪除成功！');
    }
    // 切換用戶的啟用狀態
    public function deactivate(Request $request, User $user)
    {
        $isActive = $request->input('is_active');
        $user->is_active = $isActive;
        $user->save();

        return redirect()->route('users.index')->with('success', 'User status updated successfully.');
    }

    public function edit()
    {
        $user = auth()->user(); // 取得當前登入用戶
        return view('users.edit', compact('user'));
    }

    public function search(Request $request)
    {
        $query = $request->query('query', '');

        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['arcade-owner', 'machine-owner']);
                });
        })
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }
}
