<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\Contact;
use App\Models\Service;
use App\Models\Actualite;
use App\Models\Newsletter;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'           => User::count(),
            'services'        => Service::count(),
            'roles'           => Role::count(),
            'permissions'     => Permission::count(),
            'actualites'      => Actualite::count(),
            'reports'         => Report::count(),
            'newsletter'      => Newsletter::count(),
            'contacts'        => Contact::count(),
            'contacts_unread' => Contact::where('read', false)->count(),
        ];

        $recentContacts = Contact::where('read', false)
            ->latest()
            ->take(6)
            ->get();

        $recentActualites = Actualite::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentContacts', 'recentActualites'));
    }
}
