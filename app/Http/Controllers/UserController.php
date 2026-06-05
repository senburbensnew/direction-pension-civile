<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['userType', 'roles', 'service']);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles    = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();

        return view('admin.users.create', compact('roles', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'firstname'             => 'nullable|string|max:255',
            'lastname'              => 'nullable|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'phone'                 => 'nullable|string|max:30',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'nullable|string|exists:roles,name',
            'service_id'            => 'nullable|integer|exists:services,id',
            'user_type'             => 'nullable|string|in:fonctionnaire,pensionnaire,institution',
            'pension_code'          => 'nullable|string|max:50',
            'nif'                   => 'nullable|string|max:20',
            'ninu'                  => 'nullable|string|max:20',
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user = User::create($validated);

        if ($role) {
            $user->assignRole($role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $roles    = Role::orderBy('name')->get();
        $services = Service::orderBy('nom')->get();

        return view('admin.users.edit', compact('user', 'roles', 'services'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'firstname'    => 'nullable|string|max:255',
            'lastname'     => 'nullable|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:30',
            'password'     => 'nullable|string|min:8|confirmed',
            'role'         => 'nullable|string|exists:roles,name',
            'service_id'   => 'nullable|integer|exists:services,id',
            'user_type'    => 'nullable|string|in:fonctionnaire,pensionnaire,institution',
            'pension_code' => 'nullable|string|max:50',
            'nif'          => 'nullable|string|max:20',
            'ninu'         => 'nullable|string|max:20',
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->syncRoles($role ? [$role] : []);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'Modifications enregistrées.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Le compte de {$user->name} a été {$label}.");
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}
