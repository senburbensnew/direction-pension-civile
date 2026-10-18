function deleteDocument(documentId) {
    if (!confirm('Are you sure you want to delete this file?')) {
        return;
    }

    const form = document.getElementById('delete-document-form');
    form.action = `/demandedocument/${documentId}`;
    form.submit();
}

window.deleteDocument = deleteDocument;

function bootPage() {
    document.addEventListener('DOMContentLoaded', function() {
            // Initialize files storage for all file inputs
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input._files = [];
            });

            function createPreviewElement(file, index, input) {
                const filePreview = document.createElement('div');
                filePreview.className = 'ml-5 flex items-center justify-between p-2 bg-gray-50 rounded mt-2 group';

                // Left section with icon and file info
                const leftSection = document.createElement('div');
                leftSection.className = 'flex items-center space-x-3';

                // File type icon
                const icon = document.createElement('div');
                if (file.type.startsWith('image/')) {
                    icon.innerHTML = `
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                `;
                    icon.innerHTML = '';
                } else {
                    icon.innerHTML = `
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                `;
                }
                leftSection.appendChild(icon);

                // File info
                const fileInfo = document.createElement('div');
                fileInfo.innerHTML = `
                <div class="text-sm text-gray-600">
                    <div class="font-medium">${file.name}</div>
                    <div class="text-xs text-gray-500">${formatFileSize(file.size)}</div>
                </div>
            `;
                // leftSection.appendChild(fileInfo);

                // Preview link for PDFs
                if (file.type === 'application/pdf') {
                    const pdfUrl = URL.createObjectURL(file);
                    const viewLink = document.createElement('a');
                    viewLink.href = pdfUrl;
                    viewLink.target = '_blank';
                    viewLink.className = 'text-blue-600 hover:text-blue-800 text-sm ml-3';
                    viewLink.textContent = 'Aperçu';
                    viewLink.addEventListener('click', () => {
                        setTimeout(() => URL.revokeObjectURL(pdfUrl), 1000);
                    });
                    leftSection.appendChild(fileInfo);
                    leftSection.appendChild(viewLink);
                }

                // Image preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imgPreview = document.createElement('img');
                        imgPreview.src = e.target.result;
                        imgPreview.className = 'h-16 w-16 object-cover rounded ml-3';
                        leftSection.appendChild(imgPreview);
                        leftSection.appendChild(fileInfo);
                    };
                    reader.readAsDataURL(file);
                }

                filePreview.appendChild(leftSection);

                // Remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className =
                    'text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity';
                removeBtn.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            `;
                removeBtn.addEventListener('click', () => {
                    // Remove file from storage
                    input._files.splice(index, 1);
                    updateFileInput(input);
                    renderPreviews(input);
                });

                filePreview.appendChild(removeBtn);
                return filePreview;
            }

            function updateFileInput(input) {
                const dt = new DataTransfer();
                input._files.forEach(file => dt.items.add(file));
                input.files = dt.files;
            }

            function renderPreviews(input) {
                const container = input.closest('.document-upload').querySelector('.preview-container');
                container.innerHTML = '';
                input._files.forEach((file, index) => {
                    container.appendChild(createPreviewElement(file, index, input));
                });
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Handle file selection
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function(e) {
                    const files = Array.from(this.files);

                    if (this.multiple) {
                        this._files.push(...files);
                    } else {
                        this._files = files;
                    }

                    updateFileInput(this);
                    renderPreviews(this);
                });
            });
        });

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPage);
} else {
    bootPage();
}
