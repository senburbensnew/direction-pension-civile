<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Liste des permissions
     */
    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(15);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Enregistrer une permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Permission::create([
            'name' => trim($validated['name']),
            'description' => blank($validated['description'] ?? null) ? null : trim($validated['description']),
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission créée avec succès.');
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Mettre à jour la permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,'.$permission->id,
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $permission->update([
            'name' => trim($validated['name']),
            'description' => blank($validated['description'] ?? null) ? null : trim($validated['description']),
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission mise à jour avec succès.');
    }

    /**
     * Supprimer une permission
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission supprimée avec succès.');
    }
}
