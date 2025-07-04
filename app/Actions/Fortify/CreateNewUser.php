<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role; // 引入 Role 模型

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'phone' => $input['phone'],
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'parent_id' => $input['parent_id'],
            'is_active' => true, // 新增：註冊時預設為啟用狀態
        ]);

        // 指定超級管理員 Email
        $superAdminEmails = ['admin@tg25.win', 'jd551225@gmail.com'];

        if (in_array(strtolower($user->email), array_map('strtolower', $superAdminEmails))) {
            // 確保 'admin' 角色存在，如果不存在則創建它
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $user->assignRole($adminRole);
        } else {
            // 預設分配給其他用戶的角色，例如 'user' 或其他您系統中定義的基礎角色
            // 如果您沒有預設角色，可以將此 else 區塊移除或留空
            // $defaultRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
            // $user->assignRole($defaultRole);
        }

        return $user;
    }
}
