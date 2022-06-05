<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $permissions = [
            [
                'name' => 'Create Role',
                'slug' => 'create-role'
            ],
            [
                'name' => 'Read Role',
                'slug' => 'read-role'
            ],
            [
                'name' => 'Update Role',
                'slug' => 'update-role'
            ],
            [
                'name' => 'Delete Role',
                'slug' => 'delete-role'
            ],
            // can attach permission, give to users
            [
                'name' => 'Manage Role',
                'slug' => 'manage-role'
            ],
            [
                'name' => 'Create Permission',
                'slug' => 'create-permission'
            ],
            [
                'name' => 'Read Permission',
                'slug' => 'read-permission'
            ],
            [
                'name' => 'Update Permission',
                'slug' => 'update-permission'
            ],
            [
                'name' => 'Delete Permission',
                'slug' => 'delete-permission'
            ],
            // can attach role, give to users
            [
                'name' => 'Manage Permission',
                'slug' => 'manage-permission'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $admin_role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin'
        ]);

        $admin_user = User::create([
            'name' => 'Axel Tahmid',
            'email' => 'super@tahmid.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);

        $admin_user->roles()->attach($admin_role);

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            // $admin_role->permissions()->attach($permission); // both works, pivot table
            $permission->roles()->attach($admin_role);
            $admin_user->permissions()->attach($permission);
        }
    }
}
