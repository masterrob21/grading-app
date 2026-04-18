<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create permissions
        Permission::create(['name' => 'view users', 'guard_name' => 'web', 'module' => 'user']);
        Permission::create(['name' => 'add users', 'guard_name' => 'web', 'module' => 'user']);
        Permission::create(['name' => 'edit users', 'guard_name' => 'web', 'module' => 'user']);
        Permission::create(['name' => 'delete users', 'guard_name' => 'web', 'module' => 'user']);

        Permission::create(['name' => 'view roles', 'guard_name' => 'web', 'module' => 'role']);
        Permission::create(['name' => 'add roles', 'guard_name' => 'web', 'module' => 'role']);
        Permission::create(['name' => 'edit roles', 'guard_name' => 'web', 'module' => 'role']);
        Permission::create(['name' => 'delete roles', 'guard_name' => 'web', 'module' => 'role']);

        Permission::create(['name' => 'view departments', 'guard_name' => 'web', 'module' => 'department']);
        Permission::create(['name' => 'add departments', 'guard_name' => 'web', 'module' => 'department']);
        Permission::create(['name' => 'edit departments', 'guard_name' => 'web', 'module' => 'department']);
        Permission::create(['name' => 'delete departments', 'guard_name' => 'web', 'module' => 'department']);

        Permission::create(['name' => 'view students', 'guard_name' => 'web', 'module' => 'student']);
        Permission::create(['name' => 'add students', 'guard_name' => 'web', 'module' => 'student']);
        Permission::create(['name' => 'edit students', 'guard_name' => 'web', 'module' => 'student']);
        Permission::create(['name' => 'delete students', 'guard_name' => 'web', 'module' => 'student']);
        Permission::create(['name' => 'upload bulk students', 'guard_name' => 'web', 'module' => 'student']);

        Permission::create(['name' => 'view academic years', 'guard_name' => 'web', 'module' => 'academic_year']);
        Permission::create(['name' => 'add academic years', 'guard_name' => 'web', 'module' => 'academic_year']);
        Permission::create(['name' => 'edit academic years', 'guard_name' => 'web', 'module' => 'academic_year']);
        Permission::create(['name' => 'delete academic years', 'guard_name' => 'web', 'module' => 'academic_year']);

        Permission::create(['name' => 'view courses', 'guard_name' => 'web', 'module' => 'course']);
        Permission::create(['name' => 'add courses', 'guard_name' => 'web', 'module' => 'course']);
        Permission::create(['name' => 'edit courses', 'guard_name' => 'web', 'module' => 'course']);
        Permission::create(['name' => 'delete courses', 'guard_name' => 'web', 'module' => 'course']);

        Permission::create(['name' => 'view course users', 'guard_name' => 'web', 'module' => 'course_user']);
        Permission::create(['name' => 'add course users', 'guard_name' => 'web', 'module' => 'course_user']);
        Permission::create(['name' => 'edit course users', 'guard_name' => 'web', 'module' => 'course_user']);
        Permission::create(['name' => 'delete course users', 'guard_name' => 'web', 'module' => 'course_user']);

        Permission::create(['name' => 'view marks', 'guard_name' => 'web', 'module' => 'mark']);
        Permission::create(['name' => 'add marks', 'guard_name' => 'web', 'module' => 'mark']);
        Permission::create(['name' => 'edit marks', 'guard_name' => 'web', 'module' => 'mark']);
        Permission::create(['name' => 'delete marks', 'guard_name' => 'web', 'module' => 'mark']);  
        Permission::create(['name' => 'upload bulk marks', 'guard_name' => 'web', 'module' => 'mark']);  

        Permission::create(['name' => 'view assessments', 'guard_name' => 'web', 'module' => 'assessment']);
        Permission::create(['name' => 'add assessments', 'guard_name' => 'web', 'module' => 'assessment']);
        Permission::create(['name' => 'edit assessments', 'guard_name' => 'web', 'module' => 'assessment']);
        Permission::create(['name' => 'delete assessments', 'guard_name' => 'web', 'module' => 'assessment']);

        Permission::create(['name' => 'view enrollments', 'guard_name' => 'web', 'module' => 'enrollment']);
        Permission::create(['name' => 'add enrollments', 'guard_name' => 'web', 'module' => 'enrollment']);
        Permission::create(['name' => 'edit enrollments', 'guard_name' => 'web', 'module' => 'enrollment']);
        Permission::create(['name' => 'delete enrollments', 'guard_name' => 'web', 'module' => 'enrollment']);
        Permission::create(['name' => 'upload bulk enrollments', 'guard_name' => 'web', 'module' => 'enrollment']);
        
        Permission::create(['name' => 'view dashboard', 'guard_name' => 'web', 'module' => 'navigation']);
        Permission::create(['name' => 'view settings', 'guard_name' => 'web', 'module' => 'navigation']);

        Permission::create(['name' => 'view role permissions', 'guard_name' => 'web', 'module' => 'role_permission']);
        Permission::create(['name' => 'edit role permissions', 'guard_name' => 'web', 'module' => 'role_permission']);

        // create roles
        Role::create(['name' => 'admin', 'guard_name' => 'web'])->givePermissionTo(Permission::all());
        Role::create(['name' => 'lead lecturer', 'guard_name' => 'web'])->givePermissionTo([
            'view students',
            'add students',
            'edit students',
            'delete students',
            'view academic years',
            'view courses',
            'view course users',
            'view marks',
            'add marks',
            'edit marks',
            'delete marks',
            'add enrollments',
            'edit enrollments',
            'delete enrollments',
        ]);
        Role::create(['name' => 'lecturer', 'guard_name' => 'web'])->givePermissionTo([
            'view marks',
            'add marks',
            'edit marks',
            'delete marks',
            'view dashboard',
        ]);

        // assign admin role to the default user
        $user = User::where('email', 'admin@example.com')->first();
        if ($user) {
            $user->assignRole('admin');
        }

    }
}