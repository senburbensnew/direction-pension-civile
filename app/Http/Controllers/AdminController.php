<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function toggleMaintenance()
    {
        $current = DB::table('parameters')
            ->where('name', 'is_maintenance_mode')
            ->value('value');

        $next = $current === 'true' ? 'false' : 'true';

        DB::table('parameters')
            ->where('name', 'is_maintenance_mode')
            ->update(['value' => $next]);

        Cache::forget('is_maintenance_mode');

        $label = $next === 'true' ? 'activé' : 'désactivé';

        return redirect()
            ->back()
            ->with('success', "Mode maintenance {$label}.");
    }
}