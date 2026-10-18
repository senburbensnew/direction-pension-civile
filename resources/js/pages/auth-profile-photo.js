function bootAuthProfilePhoto() {
    document.getElementById('profile_photo')?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        const preview = document.getElementById('photo-preview');
        const placeholder = document.getElementById('photo-placeholder');
        if (!file || !preview) {
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Le fichier dépasse 5 Mo. Choisissez une image plus légère.');
            e.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            preview.src = ev.target.result;
            preview.classList.remove('hidden');
            placeholder?.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAuthProfilePhoto);
} else {
    bootAuthProfilePhoto();
}
