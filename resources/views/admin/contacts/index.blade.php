@extends('layouts.admin')

@section('title', 'Messages de contact')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Messages de contact</span>
@endsection

@section('content')
<div class="space-y-4">

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ── Header ────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Messages reçus</h1>
            @if($unreadCount > 0)
                <p class="text-sm text-orange-600 mt-0.5">
                    <span class="inline-block w-2 h-2 rounded-full bg-orange-500 mr-1"></span>
                    {{ $unreadCount }} non lu{{ $unreadCount > 1 ? 's' : '' }}
                </p>
            @else
                <p class="text-sm text-gray-400 mt-0.5">Tous les messages ont été lus</p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('admin.contacts.markAllRead') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                        <i class="fas fa-check-double"></i> Tout marquer comme lu
                    </button>
                </form>
            @endif

            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Rechercher…"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" onchange="this.form.submit()"
                        class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Non lus</option>
                    <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Lus</option>
                </select>
            </form>
        </div>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="w-2 px-4 py-3"></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Expéditeur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Sujet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                    @php
                        $subjects = [
                            'pension'    => ['label' => 'Pensions',    'color' => 'bg-blue-100 text-blue-700'],
                            'documents'  => ['label' => 'Documents',   'color' => 'bg-purple-100 text-purple-700'],
                            'rendezvous' => ['label' => 'Rendez-vous', 'color' => 'bg-orange-100 text-orange-700'],
                            'autre'      => ['label' => 'Autre',       'color' => 'bg-gray-100 text-gray-600'],
                        ];
                        $subj = $subjects[$contact->subject] ?? ['label' => $contact->subject, 'color' => 'bg-gray-100 text-gray-600'];
                    @endphp
                    <tr class="{{ !$contact->read ? 'bg-blue-50/50' : '' }} hover:bg-gray-50 transition-colors">

                        {{-- Unread dot --}}
                        <td class="px-4 py-3">
                            @if(!$contact->read)
                                <span class="block w-2 h-2 rounded-full bg-blue-500"></span>
                            @endif
                        </td>

                        {{-- Sender --}}
                        <td class="px-4 py-3">
                            <p class="font-{{ !$contact->read ? 'semibold' : 'medium' }} text-gray-800 whitespace-nowrap">
                                {{ $contact->first_name }} {{ $contact->last_name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $contact->email }}</p>
                        </td>

                        {{-- Subject badge --}}
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $subj['color'] }}">
                                {{ $subj['label'] }}
                            </span>
                        </td>

                        {{-- Preview --}}
                        <td class="px-4 py-3 text-gray-500 max-w-xs hidden md:table-cell">
                            <span class="{{ !$contact->read ? 'font-medium text-gray-700' : '' }}">
                                {{ Str::limit($contact->message, 65) }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-4 py-3 text-gray-400 whitespace-nowrap text-xs">
                            <span title="{{ $contact->created_at->format('d/m/Y H:i') }}">
                                {{ $contact->created_at->diffForHumans() }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.contacts.show', $contact->id) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors">
                                    <i class="fas fa-eye text-[10px]"></i> Lire
                                </a>

                                @if($contact->read)
                                    <form action="{{ route('admin.contacts.markUnread', $contact->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Marquer comme non lu"
                                                class="p-1.5 text-gray-300 hover:text-blue-500 transition-colors rounded">
                                            <i class="fas fa-envelope text-xs"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.contacts.markRead', $contact->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Marquer comme lu"
                                                class="p-1.5 text-blue-400 hover:text-gray-400 transition-colors rounded">
                                            <i class="fas fa-envelope-open text-xs"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Supprimer"
                                            class="p-1.5 text-gray-300 hover:text-red-500 transition-colors rounded">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-4xl text-gray-200 mb-3 block"></i>
                            <p class="text-gray-400 font-medium">Aucun message trouvé</p>
                            @if(request('q') || request('status'))
                                <a href="{{ route('admin.contacts.index') }}" class="text-xs text-blue-500 hover:underline mt-1 block">
                                    Effacer les filtres
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($contacts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $contacts->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
