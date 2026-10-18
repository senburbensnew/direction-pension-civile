export function initProfilePictureCrop() {
    document.querySelectorAll('[data-dpc-profile-picture]').forEach((root) => {
        if (root.dataset.profilePictureReady === '1') {
            return;
        }
        root.dataset.profilePictureReady = '1';

        const fileInput = root.querySelector('#photoUpload');
        const dropzone = root.querySelector('#dropzone');
        const preview = root.querySelector('#photoPreview');
        const previewContainer = root.querySelector('#photoPreviewContainer');
        const errorElement = root.querySelector('#uploadError');

        const showError = (message) => {
            if (!errorElement) {
                return;
            }
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        };

        const clearError = () => errorElement?.classList.add('hidden');

        const previewFile = (file) => {
            if (!file?.type.startsWith('image/')) {
                showError('Please upload a valid image file');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showError('File size exceeds 5MB limit');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                if (preview) {
                    preview.src = e.target.result;
                }
                previewContainer?.classList.remove('hidden');
                dropzone?.classList.add('border-solid');
                clearError();
            };
            reader.readAsDataURL(file);
        };

        fileInput?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (file) {
                previewFile(file);
            }
        });

        root.querySelector('[data-profile-picture-remove]')?.addEventListener('click', () => {
            if (fileInput) {
                fileInput.value = '';
            }
            if (preview) {
                preview.src = '';
            }
            previewContainer?.classList.add('hidden');
            dropzone?.classList.remove('border-solid');
            clearError();
        });

        dropzone?.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropzone?.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropzone?.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.remove('border-blue-500', 'bg-blue-50');
            const file = e.dataTransfer.files?.[0];
            if (file && fileInput) {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                previewFile(file);
            }
        });
    });
}
