<div class="order-3 flex flex-col items-center space-y-3">
    @if ($showLabel)
        <label class="block font-semibold text-gray-700 mb-1">Photo</label>
    @endif

    <div class="relative group" id="dropzone">
        <input type="file" id="photoUpload" accept="image/*" class="hidden" onchange="previewPhoto(event)"
            name="profile_photo">

        <label for="photoUpload"
            class="cursor-pointer flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl w-32 h-32 hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 ease-in-out group-hover:scale-102 @error('profile_photo') border-red-500 @enderror"
            ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">

            <svg class="w-8 h-8 text-gray-400 mb-2 group-hover:text-blue-500" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>

            <span
                class="text-sm text-gray-500 group-hover:text-blue-600 text-center @error('profile_photo') text-red-500 @enderror">Cliquez
                ou glissez-déposez</span>
        </label>

        <div id="photoPreviewContainer" class="hidden absolute inset-0 w-32 h-32">
            <img id="photoPreview" class="w-full h-full object-cover rounded-xl border border-gray-200" />
            <button type="button"
                class="absolute -top-2 -right-2 bg-white rounded-full p-1 shadow-sm border hover:bg-red-50 transition-colors"
                onclick="removePhoto()">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- <p class="text-xs text-gray-500 text-center">JPEG, PNG (Max 5MB)</p> --}}
    <p id="uploadError" class="text-red-500 text-sm hidden"></p>
    {{--     @error('profile_photo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror --}}
</div>

{{-- @push('scripts') --}}
<script>
    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.classList.add('border-blue-500', 'bg-blue-50');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        e.target.classList.remove('border-blue-500', 'bg-blue-50');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            previewPhoto({
                target: {
                    files
                }
            });
        }
    }

    function previewPhoto(event) {
        const file = event.target.files[0];
        const errorElement = document.getElementById('uploadError');

        if (file && file.type.startsWith('image/')) {
            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                errorElement.textContent = 'File size exceeds 5MB limit';
                errorElement.classList.remove('hidden');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('photoPreview').src = e.target.result;
                document.getElementById('photoPreviewContainer').classList.remove('hidden');
                document.getElementById('dropzone').classList.add('border-solid');
                errorElement.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            errorElement.textContent = 'Please upload a valid image file';
            errorElement.classList.remove('hidden');
        }
    }

    function removePhoto() {
        document.getElementById('photoUpload').value = '';
        document.getElementById('photoPreview').src = '';
        document.getElementById('photoPreviewContainer').classList.add('hidden');
        document.getElementById('dropzone').classList.remove('border-solid');
        document.getElementById('uploadError').classList.add('hidden');
    }
</script>
{{-- @endPush --}}
