<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Liste des roles
     */
    public function index()
    {
        $roles = Role::query()
            ->with(['permissions'])
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        $permissions = Permission::query()->orderBy('name')->get();
        $permissionOptions = $this->permissionListboxOptions($permissions);
        $assignedPermissions = array_values(array_filter((array) old('permissions', [])));

        return view('admin.roles.index', compact(
            'roles',
            'permissionOptions',
            'assignedPermissions',
        ));
    }

    /**
     * Enregistrer un rôle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_]*$/',
                'unique:roles,name',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => strtolower(trim($validated['name'])),
            'description' => blank($validated['description'] ?? null) ? null : trim($validated['description']),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Mettre à jour le rôle
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'regex:/^[a-z][a-z0-9_]*$/',
                'unique:roles,name,'.$role->id,
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update([
            'name' => strtolower(trim($validated['name'])),
            'description' => blank($validated['description'] ?? null) ? null : trim($validated['description']),
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'Impossible de supprimer un rôle assigné à des utilisateurs.');
        }

        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Permission>  $permissions
     * @return list<array{value: string, label: string, description: ?string}>
     */
    private function permissionListboxOptions($permissions): array
    {
        return $permissions->map(function (Permission $permission) {
            $name = $permission->name;

            return [
                'value' => $name,
                'label' => $name,
                'description' => filled($permission->description) ? $permission->description : null,
            ];
        })->values()->all();
    }
}
