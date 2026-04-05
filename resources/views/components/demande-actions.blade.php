@props(['demande' => null])

@if (!$demande || $demande->isDraft())
    <div class="mt-6 flex items-center gap-2">
        <input type="checkbox" name="is_urgent" id="is_urgent" value="1" class="w-4 h-4 accent-red-600">
        <label for="is_urgent" class="text-sm font-medium text-red-600 cursor-pointer">Marquer comme urgent</label>
    </div>
    <div class="mt-4 flex justify-end gap-3">
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
