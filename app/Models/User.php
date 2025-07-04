<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; // 引入 SoftDeletes
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Arcade; // Import the Arcade model
use App\Models\Machine; // Import the Machine model
use App\Models\MachineAuthKey; // Import the MachineAuthKey model
/**
 * 
 *
 * @method bool hasRole(string|array|\Spatie\Permission\Contracts\Role $roles)
 * @method void sendEmailVerificationNotification()
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $phone
 * @property bool $is_member
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property int|null $created_by
 * @property int|null $parent_id
 * @property string|null $remember_token
 * @property array|null $sidebar_permissions
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $invitation_code
 * @property-read \App\Models\Arcade|null $arcade
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $children
 * @property-read int|null $children_count
 * @property-read mixed $primary_role
 * @property-read bool $role
 * @property-read int|null $machines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arcade> $managedArcades
 * @property-read int|null $managed_arcades_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read User|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read string $profile_photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereInvitationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsMember($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        Notifiable,
        \Illuminate\Auth\MustVerifyEmail,
        TwoFactorAuthenticatable, // SoftDeletes trait 應該在 HasRoles 之前或之後都可以
        SoftDeletes,
        Notifiable,
        HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = ['phone', 'name', 'email', 'password', 'is_member', 'is_active', 'parent_id', 'created_by', 'email_verified_at', 'sidebar_permissions', 'invitation_code'];


    // 定義與機台管理員的關聯（改為 arcade 關聯）
    public function arcade()
    {
        return $this->hasOne(Arcade::class, 'owner_id');
    }

    // 定義與機器管理員的關聯
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

    /**
     * Get the arcades owned by the user.
     * Defines the one-to-many relationship where a User (owner) has many Arcades.
     */
    public function arcades()
    {
        return $this->hasMany(Arcade::class, 'owner_id');
    }
    public function isArcadeStaff(): bool
    {
        // Assuming you are using spatie/laravel-permission
        // to manage roles and permissions.
        return $this->hasRole('arcade-staff');
    }
    public function isMachineStaff(): bool
    {
        // Assuming you are using spatie/laravel-permission
        // to manage roles and permissions.
        return $this->hasRole('machine-staff');
    }

    /**
     * Get the machine auth keys owned by the user.
     */
    public function machineAuthKeysOwned()
    {
        return $this->hasMany(MachineAuthKey::class, 'owner_id');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($user) {
            // 如果是硬刪除 (非軟刪除觸發)
            if (!$user->isForceDeleting()) {
                // 處理下屬員工的 parent_id，將其設為 null
                $user->children()->update(['parent_id' => null]);

                // 處理該用戶擁有的 Arcade，將其 owner_id 設為 null (或根據業務邏輯轉移)
                $user->arcades()->update(['owner_id' => null]);

                // 處理該用戶擁有的 Machine，將其 owner_id 設為 null (或根據業務邏輯轉移)
                Machine::where('owner_id', $user->id)->update(['owner_id' => null]);

                // 處理該用戶擁有的 MachineAuthKey，可以選擇刪除或設為無主
                // $user->machineAuthKeysOwned()->delete(); // 如果金鑰必須有擁有者，則刪除
                $user->machineAuthKeysOwned()->update(['owner_id' => null]); // 或者設為無主
            }
        });
    }
}
