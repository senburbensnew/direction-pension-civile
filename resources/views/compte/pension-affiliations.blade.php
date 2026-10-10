<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes codes pension</h2>
    </x-slot>

    <div class="py-8 px-4 max-w-4xl mx-auto space-y-8">
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
        @endif

        @foreach($beneficiaires as $beneficiaire)
            <section class="bg-white border rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $beneficiaire->displayName() }}
                    @if($beneficiaire->is_mineur)
                        <span class="text-xs font-normal text-amber-700">(pensionné mineur)</span>
                    @endif
                </h3>

                <ul class="mt-4 divide-y">
                    @forelse($beneficiaire->affiliations as $affiliation)
                        <li class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <span class="font-mono font-medium">{{ $affiliation->code_pension }}</span>
                                @if($affiliation->is_primary)
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Principal</span>
                                @endif
                            </div>
                            @if($beneficiaire->affiliations->count() > 1)
                                <form method="POST" action="{{ route('compte.affiliations.destroy', $affiliation) }}" onsubmit="return confirm('Retirer ce code ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm hover:underline">Retirer</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-3 text-gray-500">Aucun code rattaché.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('compte.affiliations.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4 items-end border-t pt-6">
                    @csrf
                    <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire->id }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ajouter un code</label>
                        <input type="text" name="code_pension" class="w-full rounded-md border-gray-300" placeholder="8-12345 ou 7-AB123" required>
                        @error('code_pension')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300">
                            Définir comme principal
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
                    </div>
                </form>
            </section>
        @endforeach

        <p class="text-sm text-gray-600">
            <a href="{{ route('compte.mandats.index') }}" class="text-indigo-700 font-medium hover:underline">Demander un mandat</a>
            pour agir au nom d’un autre pensionné.
        </p>
    </div>
</x-app-layout>
