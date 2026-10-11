@if($rdvPending ?? true)
    <form
        method="POST"
        action="{{ $refuserRoute ?? route('rencontres.pilotage.refuser', $demande) }}"
        class="border-t border-gray-100 pt-4 space-y-3"
        onsubmit="return confirm('Confirmer l’annulation de ce rendez-vous ?');"
    >
        @csrf
        <p class="text-sm font-semibold text-red-800">
            <i class="fa-solid fa-ban mr-1"></i>
            Annuler le rendez-vous
        </p>
        <textarea
            name="commentaire"
            rows="2"
            required
            maxlength="2000"
            placeholder="Motif de l’annulation (obligatoire)…"
            class="w-full text-sm border border-red-200 rounded-lg px-3 py-2 focus:ring-red-200 focus:outline-none"
        >{{ old('commentaire') }}</textarea>
        @error('commentaire')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
        <button
            type="submit"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg"
        >
            Confirmer l’annulation
        </button>
    </form>
@endif
