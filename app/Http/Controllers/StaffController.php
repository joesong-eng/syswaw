<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function staff()
    {
        $staffs = User::find(Auth::id())->children()->with('roles')->get();
        return view('arcade.staff', compact('staffs'));
    }
    public function index()
    {
        $user = Auth::user();
        $query = User::with('roles'); // 開始建立查詢

        if ($user->hasRole('admin')) {
            // Admin 可以看到所有 staff (可以考慮只顯示特定 staff 角色)
            // 例如：$query->role(['arcade-staff', 'machine-staff']);
            // 目前還是顯示所有用戶，但至少預載了角色
        } elseif ($user->hasRole('arcade-owner')) {
            $view = 'arcade.staff';
            $query->where('parent_id', $user->id);
        } elseif ($user->hasRole('machine-owner')) {
            $view = 'machine.staff';
            $query->where('parent_id', $user->id);
        } else {
            return view('dashboard');

            // $query->whereRaw('1 = 0'); // 永遠為 false 的條件
        }

        $staffs = $query->get();
        $roles = Role::all();

        // Determine the view based on role
        $view = $user->hasRole('machine-owner') ? 'machine.staff' : 'arcade.staff';

        return view($view, compact('staffs', 'roles'));
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
            'is_active' => 0, // 確保 is_active 欄位有值
        ]);
        $user->assignRole($validated['role']); // 分配角色
        if ($request->filled('filter_user_id')) {
            $user->parent_id = $validated['filter_user_id'];
            $user->save();
        }
        return redirect()->back()->with('success', '用戶創建成功！');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'sidebar_permissions' => 'nullable|array', // 新增：接收側邊欄權限
        ]);

        $res = $user->update([
            'name' => $validated['name'],
            'sidebar_permissions' => $request->input('sidebar_permissions'),
        ]);
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->back()->with('success', __('msg.staff_updated_successfully'));
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
    //     return redirect()->back()->with('success', '用戶更新成功');// 返回成功訊息或重新導向
    // }
    public function logs()
    {
        $logs = auth()->user()->logs()->latest()->get(); // 假設用戶有一個 `logs` 關聯
        return view('user.logs', compact('logs'));
    }
    public function destroy(User $user)
    {
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
        // dd($request->toArray());
        $isActive = $request->input('is_active');
        $user->is_active = $isActive;
        $user->save();
        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function edit()
    {
        $user = auth()->user(); // 取得當前登入用戶
        return view('users.edit', compact('user'));
    }

    public function generateInvitationCode(User $user)
    {
        $code = \Str::random(6); // 生成 6 位隨機字串
        $user->update(['invitation_code' => $code]);
        return redirect()->back()->with('success', __('msg.invitation_code_generated'));
    }
}
