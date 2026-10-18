<div class="order-3 flex flex-col items-center space-y-3" data-dpc-profile-picture>
    @if ($showLabel)
        <label class="block font-semibold text-gray-700 mb-1">Photo</label>
    @endif

    <div class="relative group" id="dropzone">
        <input type="file" id="photoUpload" accept="image/*" class="hidden" name="profile_photo">

        <label for="photoUpload"
            class="cursor-pointer flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl w-32 h-32 hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 ease-in-out group-hover:scale-102 @error('profile_photo') border-red-500 @enderror">

            <svg class="w-8 h-8 text-gray-400 mb-2 group-hover:text-blue-500" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>

            <span
                class="text-base text-gray-500 group-hover:text-blue-600 text-center @error('profile_photo') text-red-500 @enderror">Cliquez
                ou glissez-déposez</span>
        </label>

        <div id="photoPreviewContainer" class="hidden absolute inset-0 w-32 h-32">
            <img id="photoPreview" class="w-full h-full object-cover rounded-xl border border-gray-200" />
            <button type="button" data-profile-picture-remove
                class="absolute -top-2 -right-2 bg-white rounded-full p-1 shadow-sm border hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- <p class="text-xs text-gray-500 text-center">JPEG, PNG (Max 5MB)</p> --}}
    <p id="uploadError" class="text-red-500 text-base hidden"></p>
    {{--     @error('profile_photo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror --}}
</div>

