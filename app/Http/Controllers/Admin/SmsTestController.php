<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Rules\Telephone;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SmsTestController extends Controller
{
    public function __construct(private SmsService $sms) {}

    public function create(): View
    {
        return view('admin.sms.test', $this->viewData());
    }

    public function store(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'to' => ['required', 'string', 'max:20', new Telephone],
            'body' => ['nullable', 'string', 'max:1600'],
        ], [
            'to.required' => 'Indiquez un numéro de destination.',
        ]);

        $to = Telephone::normalize($validated['to']);
        $body = filled($validated['body'] ?? null)
            ? (string) $validated['body']
            : $this->defaultBody();

        if (! SmsService::isProviderConfigured()) {
            return view('admin.sms.test', array_merge($this->viewData(), [
                'to' => $to,
                'body' => $validated['body'] ?? '',
                'error' => 'Le fournisseur SMS n’est pas configuré (clés API ou expéditeur manquants).',
            ]));
        }

        try {
            $this->sms->sendTest($to, $body);
        } catch (Throwable $e) {
            if (! $e instanceof RuntimeException) {
                report($e);
            }

            return view('admin.sms.test', array_merge($this->viewData(), [
                'to' => $to,
                'body' => $validated['body'] ?? '',
                'error' => $e->getMessage(),
            ]));
        }

        return redirect()
            ->route('admin.sms.test')
            ->with('success', "SMS de test envoyé au {$to}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        $summary = SmsService::configurationSummary();

        return [
            'driver' => $summary['driver'],
            'driverLabel' => $summary['driver_label'],
            'from' => $summary['from'],
            'notificationsSmsEnabled' => (bool) config('notifications.sms_enabled'),
            'providerConfigured' => SmsService::isProviderConfigured(),
            'to' => old('to', auth()->user()?->routeNotificationForTwilio() ?? '+509'),
            'body' => old('body', ''),
            'error' => null,
        ];
    }

    private function defaultBody(): string
    {
        $user = auth()->user();

        return implode("\n", [
            'DPC — Message de test SMS depuis l’administration.',
            'Date : '.now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s T'),
            'Demandé par : '.($user?->name ?? '—'),
        ]);
    }
}
