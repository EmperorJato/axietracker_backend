<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            [
                'name' => 'View Dashboard',
                'slug' => 'view-dashboard'
            ],
            [
                'name' => 'View Scholarship',
                'slug' => 'view-scholarship'
            ],
            
            [
                'name' => 'Create Scholarship',
                'slug' => 'create-scholarship'
            ],
            [
                'name' => 'Edit Scholarship',
                'slug' => 'edit-scholarship'
            ],
            [
                'name' => 'Delete Scholarship',
                'slug' => 'delete-scholarship'
            ],
            [
                'name' => 'View Investment',
                'slug' => 'view-investment'
            ],
            [
                'name' => 'View Simulator',
                'slug' => 'view-simulator'
            ],
            [
                'name' => 'View Leaderboards',
                'slug' => 'view-leaderboards'
            ],
            [
                'name' => 'View Accounts',
                'slug' => 'view-accounts'
            ],
            [
                'name' => 'Create Accounts',
                'slug' => 'create-accounts'
            ]
            ,
            [
                'name' => 'Edit Accounts',
                'slug' => 'edit-accounts'
            ]
            ,
            [
                'name' => 'Delete Accounts',
                'slug' => 'delete-accounts'
            ]
        ]);
    }
}
