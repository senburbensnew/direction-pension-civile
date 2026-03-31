@extends('layouts.admin')

@section('title', 'Message de ' . $contact->first_name)
@section('breadcrumb')
    <a href="{{ route('admin.contacts.index') }}" class="text-gray-500 hover:text-gray-800">Messages</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-700">{{ $contact->first_name }} {{ $contact->last_name }}</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.contacts.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-gray-800 text-base">
                    {{ $contact->first_name }} {{ $contact->last_name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    <a href="mailto:{{ $contact->email }}" class="hover:underline text-blue-600">{{ $contact->email }}</a>
                    &mdash; {{ $contact->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium flex-shrink-0">
                @php
                    $subjects = ['pension'=>'Pensions','documents'=>'Documents','rendezvous'=>'Rendez-vous','autre'=>'Autre'];
                @endphp
                {{ $subjects[$contact->subject] ?? $contact->subject }}
            </span>
        </div>

        {{-- Message body --}}
        <div class="px-6 py-5">
            <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $contact->message }}</p>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
            <a href="mailto:{{ $contact->email }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-reply"></i> Répondre par email
            </a>

            <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                onsubmit="return confirm('Supprimer ce message ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
