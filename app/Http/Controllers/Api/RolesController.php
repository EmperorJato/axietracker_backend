<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RolesController extends Controller
{
    public function index(){
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return response()->json(['roles' => $roles, 'permissions' => $permissions]);
    }
    
    public function create($request){
        return Role::create([
            'name' => $request['name'],
            'slug' => $request['slug'],
        ]);
      
    }

    public function store(Request $request){

        $role = $this->create($request->all());
        $permissions = Permission::whereIn('slug', $request->permissions)->get()->pluck('id')->toArray();
        $role->permissions()->sync($permissions);
    }

    public function update(Request $request, $id){
        $role = Role::find($id);
        $role->name = $request->name;
        $permissions = Permission::whereIn('slug', $request->permissions)->get()->pluck('id')->toArray();
        $role->permissions()->sync($permissions);
        if($role->save()){
            return response()->json(['message' => 'Updated Successfully', 'role' => $role, 'permissions' => $role->permissions()]);
        }
        return response()->json(['message' => 'Something went wrong'], 500);
    }
}
