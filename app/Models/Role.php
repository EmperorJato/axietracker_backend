<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPermissions;

class Role extends Model
{
    use HasFactory, HasPermissions;

    public function permissions(){
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }

    public function hasPermissionTo(...$permissions){
        return $this->permissions()->whereIn('slug', $permissions)->count();
    }

    public function scopeDeveloper($query){
        return $query->where('slug', 'developer');
    }

    public function scopeManager($query){
        return $query->where('slug', 'manager');
    }

    public function scopeScholar($query){
        return $query->where('slug', 'scholar');
    }
}
