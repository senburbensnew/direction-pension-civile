<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmailTestRequest;
use App\Mail\AdminEmailTestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailTestController extends Controller
{
    public function create(): View
    {
        return view('admin.email.test', $this->viewData());
    }

    public function store(StoreEmailTestRequest $request): RedirectResponse|View
    {
        $validated = $request->validated();

        $body = filled($validated['body'] ?? null)
            ? (string) $validated['body']
            : $this->defaultBody();

        try {
            Mail::to($validated['to'])->send(
                new AdminEmailTestMail($body, $validated['subject'])
            );
        } catch (Throwable $e) {
            report($e);

            return view('admin.email.test', array_merge($this->viewData(), [
                'to' => $validated['to'],
                'subject' => $validated['subject'],
                'body' => $validated['body'] ?? '',
                'error' => $e->getMessage(),
            ]));
        }

        return redirect()
            ->route('admin.email.test')
            ->with('success', "E-mail de test envoyé à {$validated['to']}.");
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        $mailer = config('mail.default');
        $from = config('mail.from');

        $mailerConfig = config('mail.mailers.'.$mailer);

        return [
            'mailer' => $mailer,
            'fromAddress' => is_array($from) ? ($from['address'] ?? null) : null,
            'fromName' => is_array($from) ? ($from['name'] ?? null) : null,
            'smtpHost' => is_array($mailerConfig) ? ($mailerConfig['host'] ?? null) : null,
            'mailpitUrl' => env('MAILPIT_URL', 'http://127.0.0.1:8025'),
            'to' => old('to', auth()->user()?->email),
            'subject' => old('subject', 'Test e-mail — Direction Pension Civile'),
            'body' => old('body', ''),
            'error' => null,
        ];
    }

    private function defaultBody(): string
    {
        $user = auth()->user();

        return implode("\n", [
            'Ceci est un message de test envoyé depuis l’administration du portail.',
            '',
            'Date : '.now()->timezone(config('app.timezone'))->format('d/m/Y H:i:s T'),
            'Expéditeur de la demande : '.($user?->name ?? '—').' ('.($user?->email ?? '—').')',
            '',
            'Si vous recevez ce message, la configuration mail de l’application fonctionne.',
        ]);
    }
}
