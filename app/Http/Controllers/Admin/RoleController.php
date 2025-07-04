<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // 確保正確引用基礎控制器
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // Admin (level 0) 可以看到所有角色，非 Admin 只能看到比自己層級低的角色
        if ($userRoleLevel === 0) {
            $roles = Role::all();
        } else {
            $roles = Role::where('level', '>', $userRoleLevel)->get();
        }

        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // 合併默認值
        $request->merge([
            'guard_name' => $request->input('guard_name', 'web'),
        ]);

        // 驗證輸入
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|integer|min:0', // 必須是 >= 0 的整數
            'guard_name' => 'required|string|max:255', // Spatie 要求 guard_name
        ]);

        $newRoleLevel = $request->input('level');

        // 權限檢查：非 Admin 只能新增比自己層級低的角色
        if ($userRoleLevel !== 0 && $newRoleLevel <= $userRoleLevel) {
            return redirect()->back()->with('error', '無權新增同級或上級角色');
        }

        // 創建角色
        Role::create([
            'name' => $request->name,
            'slug' => $request->slug ?? $request->name,
            'guard_name' => $request->guard_name,
            'level' => $newRoleLevel,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // 合併默認值
        $request->merge([
            'guard_name' => $request->input('guard_name', 'web'),
        ]);

        // 驗證輸入
        $request->validate([
            'name' => 'required|string|max:255',
            'guard_name' => 'required|string|max:255',
            'level' => 'required|integer|min:0',
        ]);

        $newRoleLevel = $request->input('level');

        // 權限檢查：非 Admin 只能修改比自己層級低的角色
        if ($userRoleLevel !== 0) {
            if ($role->level <= $userRoleLevel) {
                return redirect()->back()->with('error', '無權修改同級或上級角色');
            }
            if ($newRoleLevel <= $userRoleLevel) {
                return redirect()->back()->with('error', '無權將角色提升到同級或上級');
            }
        }

        // 更新角色
        $role->update($request->only('name', 'guard_name', 'level'));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    // 補充其他 RESTful 方法（可選）
    public function create()
    {
        return view('roles.create');
    }

    public function edit(Role $role)
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // 非 Admin 不能編輯同級或上級角色
        if ($userRoleLevel !== 0 && $role->level <= $userRoleLevel) {
            return redirect()->route('admin.roles.index')->with('error', '無權編輯同級或上級角色');
        }

        return view('roles.edit', compact('role'));
    }

    public function destroy(Role $role)
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // 非 Admin 不能刪除同級或上級角色
        if ($userRoleLevel !== 0 && $role->level <= $userRoleLevel) {
            return redirect()->route('admin.roles.index')->with('error', '無權刪除同級或上級角色');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function show(Role $role)
    {
        $user = Auth::user();
        $userRoleLevel = $user->primaryRole->level;

        // 非 Admin 不能查看同級或上級角色
        if ($userRoleLevel !== 0 && $role->level <= $userRoleLevel) {
            return redirect()->route('admin.roles.index')->with('error', '無權查看同級或上級角色');
        }

        return view('roles.show', compact('role'));
    }
}
