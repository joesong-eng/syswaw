<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $owner_id
 * @property int|null $manager
 * @property string|null $authorize_key
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $image_url
 * @property string|null $business_hours
 * @property string $revenue_split
 * @property int $is_active
 * @property int|null $created_by
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Machine> $machines
 * @property-read int|null $machines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $managers
 * @property-read int|null $managers_count
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Store newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereAuthorizeKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereBusinessHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereRevenueSplit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Store extends Model
{
    use HasFactory;

    protected $table = 'arcades';
    protected $fillable = ['name', 'owner_id', 'managers', 'address', 'phone', 'expires_at', 'image_url',];
    // 創建者
    public function creator()
    {
        return $this->belongsTo(User::class, 'create_by');
    }
    // 店鋪所有者
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    // 店鋪管理員
    public function managers()
    {
        return $this->belongsToMany(User::class, 'store_managers', 'store_id', 'user_id')
            ->withPivot('token')->withTimestamps();
    }
    // 多態關聯的機器
    public function machines()
    {
        return $this->morphMany(Machine::class, 'storeable');
    }
}
