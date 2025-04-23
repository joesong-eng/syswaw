<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'level', 'guard_name', 'slug'];

    // public function users()
    // {
    //     return $this->belongsToMany(User::class);
    // }
}
