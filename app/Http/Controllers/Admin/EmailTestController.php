<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminEmailTestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailTestController extends Controller
{
    public function create(): View
    {
        return view('admin.email.test', $this->viewData());
    }

    public function store(Request $request): RedirectResponse|View
    {
        $validated = $request->validate([
            'to' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
        ], [
            'to.required' => 'Indiquez une adresse de destination.',
            'to.email' => 'L’adresse de destination n’est pas valide.',
            'subject.required' => 'Indiquez un objet.',
        ]);

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

        return [
            'mailer' => $mailer,
            'fromAddress' => is_array($from) ? ($from['address'] ?? null) : null,
            'fromName' => is_array($from) ? ($from['name'] ?? null) : null,
            'smtpHost' => config('mail.mailers.smtp.host'),
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
