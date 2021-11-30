<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Scholarship;
use App\Traits\ScholarshipTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use Illuminate\Support\Str;

class AccountsController extends Controller
{
    use ScholarshipTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $scholarship = Scholarship::where('manager_id', Auth::user()->id)->get();
        
        foreach($scholarship as $item){
            $user =  User::where('id', $item->scholar_id)->first();
            $account[] = new UserResource($user);
        }
      
       return $account;
    }

    public function roles(){
        
        $roles = Role::all()->map(function ($role) {
            $role->slugCamel = Str::camel($role->slug);
            return $role;
        })->pluck('slug', 'slugCamel');

        $permissions = Permission::all()->map(function ($role) {
            $role->slugCamel = Str::camel($role->slug);
            return $role;
        })->pluck('slug', 'slugCamel');

        return response()->json(['roles' => $roles, 'permissions' => $permissions]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
