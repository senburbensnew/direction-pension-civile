<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Models\Permission;

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
    public function store(StorePermissionRequest $request)
    {
        $validated = $request->validated();

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
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $validated = $request->validated();

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
