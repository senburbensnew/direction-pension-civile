<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\QueueMonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function __construct(private QueueMonitorService $monitor) {}

    public function index(): View
    {
        return view('admin.queue.index', [
            'driver' => $this->monitor->driver(),
            'supportsDatabaseMonitor' => $this->monitor->supportsDatabaseMonitor(),
            'pendingCount' => $this->monitor->pendingCount(),
            'failedCount' => $this->monitor->failedCount(),
            'pendingJobs' => $this->monitor->pendingJobs(),
            'failedJobs' => $this->monitor->failedJobs(),
            'maxTries' => (int) config('queue.max_tries', 5),
        ]);
    }

    public function showFailed(string $uuid): View
    {
        abort_unless($this->monitor->supportsDatabaseMonitor(), 404);

        $row = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        abort_if($row === null, 404);

        $parsed = $this->monitor->parsePayload((string) $row->payload);

        return view('admin.queue.failed-show', [
            'job' => $row,
            'parsed' => $parsed,
        ]);
    }

    public function retryFailed(string $uuid): RedirectResponse
    {
        abort_unless($this->monitor->supportsDatabaseMonitor(), 404);

        if (! DB::table('failed_jobs')->where('uuid', $uuid)->exists()) {
            return back()->with('error', 'Job introuvable.');
        }

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('success', 'Job remis en file d’attente.');
    }

    public function forgetFailed(string $uuid): RedirectResponse
    {
        abort_unless($this->monitor->supportsDatabaseMonitor(), 404);

        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('success', 'Entrée supprimée de failed_jobs.');
    }

    public function retryAllFailed(): RedirectResponse
    {
        abort_unless($this->monitor->supportsDatabaseMonitor(), 404);

        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'Tous les jobs en échec ont été remis en file.');
    }

    public function destroyPending(int $id): RedirectResponse
    {
        abort_unless($this->monitor->supportsDatabaseMonitor(), 404);

        $deleted = DB::table('jobs')->where('id', $id)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'Job introuvable ou déjà traité.');
        }

        return back()->with('success', 'Job en attente supprimé.');
    }
}
