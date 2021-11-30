<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Scholarship;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userRoles = $this->roles()->with('permissions')->get();
        $role = $userRoles->pluck('name');
        $permissions = $userRoles->pluck('permissions')->flatten(1)->pluck('slug');

        return [
            'id' => $this->id,
            'image' => $this->image,
            'name' => $this->name,
            'contact_number' => $this->contact_number,
            'email' => $this->email,
            'address' => $this->address,
            'metamask_address' => $this->metamask_address,
            'ronin_address' => $this->ronin_address,
            'role' => $role,
            'permissions' => $permissions
        ];
    }
}
