<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions {

    public function hasPermissionTo(...$permissions){
        return $this->roles()->with('permissions')->whereIn('slug', $permissions)->count() || 
            $this->roles()->whereHas('permissions', function($q) use ($permissions){
                $q->whereIn('slug', $permissions);
            })->count();
    }

    private function getPermissionIdsBySlug($permissions){
        return Permission::whereIn('slug', $permissions)->get()->pluck('id')->toArray();
    }

    public function attachPermission(...$permissions){
        $this->permissions()->attach($this->getPermissionIdsBySlug($permissions));
    }

    public function syncPermission(...$permissions){
        $this->permissions()->sync($this->getPermissionIdsBySlug($permissions));
    }

    public function detachPermission(...$permissions){
        $this->permissions()->detach($this->getPermissionIdsBySlug($permissions));
    }
}