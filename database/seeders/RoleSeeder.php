<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::insert([
            [
                'name' => 'Developer',
                'slug' => 'developer'
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager'
            ],
            [
                'name' => 'Scholar',
                'slug' => 'scholar'
            ]
        ]);

        $developerRole = Role::developer()->firstOrFail();
        $developerPermissions = Permission::whereIn('slug', ['view-dashboard'])->get()->pluck('id')->toArray();
        $developerRole->permissions()->sync($developerPermissions);
    }
}
