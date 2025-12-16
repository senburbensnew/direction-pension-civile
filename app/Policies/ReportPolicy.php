<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
public function viewAny($user)
{
    // par exemple seulement admin et institution peuvent voir le dashboard
    return $user->hasRole('admin') || $user->hasRole('institution');
}

public function view($user, Report $report)
{
    // published = public; draft = author or admin
    if ($report->status === 'published') {
        return true;
    }
    return $user->hasRole('admin') || $user->id === $report->created_by;
}

public function create($user)
{
    return $user->hasRole('admin') || $user->hasRole('institution');
}

public function update($user, Report $report)
{
    return $user->hasRole('admin') || $user->id === $report->created_by;
}

public function delete($user, Report $report)
{
    return $user->hasRole('admin');
}

public function publish($user, Report $report)
{
    return $user->hasRole('admin'); // ou autoriser role 'institution' si besoin
}

}
