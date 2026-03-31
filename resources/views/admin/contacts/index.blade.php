@extends('layouts.admin')

@section('title', 'Messages')
@section('breadcrumb')
    <span class="text-gray-700 text-sm">Messages de contact</span>
@endsection

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Messages reçus</h1>
            @if($unreadCount > 0)
                <p class="text-sm text-orange-600 mt-0.5">
                    <i class="fas fa-circle text-xs mr-1"></i>{{ $unreadCount }} non lu{{ $unreadCount > 1 ? 's' : '' }}
                </p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            {{-- Mark all read --}}
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('admin.contacts.markAllRead') }}">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium">
                        <i class="fas fa-check-double mr-1"></i> Tout marquer comme lu
                    </button>
                </form>
            @endif

            {{-- Search + filter --}}
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous</option>
                    <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Non lus</option>
                    <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Lus</option>
                </select>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 w-4"></th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Expéditeur</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Sujet</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Message</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                    <tr class="{{ !$contact->read ? 'bg-blue-50/40' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if(!$contact->read)
                                <span class="block w-2 h-2 rounded-full bg-blue-500"></span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 {{ !$contact->read ? 'font-semibold' : '' }}">
                                {{ $contact->first_name }} {{ $contact->last_name }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $contact->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $subjects = [
                                    'pension'    => 'Pensions',
                                    'documents'  => 'Documents',
                                    'rendezvous' => 'Rendez-vous',
                                    'autre'      => 'Autre',
                                ];
                            @endphp
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                                {{ $subjects[$contact->subject] ?? $contact->subject }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs">
                            {{ Str::limit($contact->message, 70) }}
                        </td>
                        <td class="px-4 py-3 text-gray-400 whitespace-nowrap text-xs">
                            {{ $contact->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.contacts.show', $contact->id) }}"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-eye"></i> Lire
                                </a>
                                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                                    onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Aucun message trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $contacts->withQueryString()->links() }}</div>
</div>
@endsection
