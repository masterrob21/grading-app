<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('name', 'asc')->get();

        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);

        return view('role.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->exists()) {
            return redirect()->route('roles.index')->with('error', 'Cannot delete role. It is assigned to users.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Show role-permission editor page.
     */
    public function permissionsIndex(Request $request)
    {
        $roles = Role::orderBy('name', 'asc')->get();
        $permissionsByModule = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        $selectedRole = null;
        $selectedPermissionNames = [];

        if ($request->filled('role')) {
            $selectedRole = Role::with('permissions')->findOrFail((int) $request->input('role'));
            $selectedPermissionNames = $selectedRole->permissions->pluck('name')->all();
        }

        return view('role_permission.index', compact('roles', 'permissionsByModule', 'selectedRole', 'selectedPermissionNames'));
    }

    /**
     * Update permissions for the selected role.
     */
    public function permissionsUpdate(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('roles.permissions.index', ['role' => $role->id])
            ->with('success', 'Role permissions updated successfully.');
    }
}