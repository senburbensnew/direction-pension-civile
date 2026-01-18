<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        Permission::create([
            'name' => strtolower(trim($request->name)),
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
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => strtolower(trim($request->name)),
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