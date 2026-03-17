<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function roles(): array
    {
        return [
            'SUPER_ADMIN', 'ADMIN', 'ACCOUNTS_MANAGER', 'ACCOUNTANT',
            'SALES_MANAGER', 'SALES_EXECUTIVE', 'PURCHASE_MANAGER',
            'PURCHASE_OFFICER', 'HR_MANAGER', 'PAYROLL_OFFICER',
            'BRANCH_MANAGER', 'AUDITOR', 'USER',
        ];
    }

    public function index()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $roles = $this->roles();

        $rolePermissions = RolePermission::all()->groupBy('role');

        return view('permissions.index', compact('permissions', 'roles', 'rolePermissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'module' => 'nullable|string|max:255',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $roles = $this->roles();

        $rolePermissions = RolePermission::where('permission_id', $permission->id)->get()->keyBy('role');

        return view('permissions.show', compact('permission', 'roles', 'rolePermissions'));
    }

    public function edit(Permission $permission)
    {
        $roles = $this->roles();

        $rolePermissions = RolePermission::where('permission_id', $permission->id)->get()->keyBy('role');

        return view('permissions.edit', compact('permission', 'roles', 'rolePermissions'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'module' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*.can_view' => 'boolean',
            'roles.*.can_create' => 'boolean',
            'roles.*.can_edit' => 'boolean',
            'roles.*.can_delete' => 'boolean',
            'roles.*.can_approve' => 'boolean',
        ]);

        $permission->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'module' => $validated['module'] ?? null,
        ]);

        // Update role permissions
        if (isset($validated['roles'])) {
            foreach ($validated['roles'] as $role => $perms) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'permission_id' => $permission->id],
                    [
                        'can_view' => !empty($perms['can_view']),
                        'can_create' => !empty($perms['can_create']),
                        'can_edit' => !empty($perms['can_edit']),
                        'can_delete' => !empty($perms['can_delete']),
                        'can_approve' => !empty($perms['can_approve']),
                    ]
                );
            }
        }

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        RolePermission::where('permission_id', $permission->id)->delete();
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
