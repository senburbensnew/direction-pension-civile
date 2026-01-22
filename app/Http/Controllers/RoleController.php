<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Liste des roles
     */
    public function index()
    {
        $roles = Role::orderBy('name')->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
    }

    /**
     * Enregistrer une permission
     */
    public function store(Request $request)
    {
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Permission $permission)
    {
    }

    /**
     * Mettre à jour la permission
     */
    public function update(Request $request, Permission $permission)
    {
    }

    /**
     * Supprimer une permission
     */
    public function destroy(Permission $permission)
    {
    }
}