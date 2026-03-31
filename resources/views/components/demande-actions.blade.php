@props(['demande' => null])

@if (!$demande || $demande->isDraft())
    <div class="mt-8 flex justify-end gap-3">
        <button type="submit" name="action" value="draft"
            class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
            Sauvegarder
        </button>
        <button type="submit" name="action" value="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Soumettre
        </button>
    </div>
@endif
