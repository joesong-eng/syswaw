<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;

/**
 * @method bool hasRole(string|array|\Spatie\Permission\Contracts\Role $roles)
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasProfilePhoto, TwoFactorAuthenticatable, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = ['phone', 'name', 'email', 'password', 'is_member', 'parent_id', 'created_by', 'email_verified_at', 'sidebar_permissions', 'invitation_code'];


    // 定義與機台管理員的關聯（改為 arcade 關聯）
    public function arcade()
    {
        return $this->hasOne(Arcade::class, 'owner_id');
    }

    // 定義與機器管理員的關聯
    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_managers');
    }
    public function managedArcades()
    {
        return $this->belongsToMany(Arcade::class, 'arcade_managers', 'user_id', 'arcade_id')
            ->withPivot('token')
            ->withTimestamps();
    }
    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // 設置是否為會員的屬性
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_member' => 'boolean',
        'sidebar_permissions' => 'array' // 明確指定為 array 類型
    ];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['profile_photo_url'];

    /**
     * Decide whether to require email verification based on user role.
     *
     * @return bool
     */
    // public function shouldVerifyEmail()
    // {
    //     // 只有不是 'user' 角色的用戶才需要驗證電子郵件
    //     return $this->role !== 'user';
    // }
    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? 'user'; // 默认角色是 user
    }
    // 關聯至店主
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // 與管理員關聯
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function getPrimaryRoleAttribute()
    {
        return $this->roles->first() ?? new Role(['name' => 'user', 'level' => 999]); // 預設角色
    }
}
