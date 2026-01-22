<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Demande;
use App\Models\Service;
use App\Models\Actualite;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class AdminDashboardController extends Controller
{
    /**
     * Liste des roles
     */

public function index()
{
    $stats = [
        'users'        => User::count(),
        'demandes'     => Demande::count(),
        'services'     => Service::count(),
        'roles'        => Role::count(),
        'permissions'  => Permission::count(),
        'actualites'   => Actualite::count(),
        'reports'      => Report::count(),
    ];

    return view('admin.dashboard', compact('stats'));
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