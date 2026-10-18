function bootAuthRegister() {
    document.querySelectorAll('input[name="primary_category"]').forEach((radio) => {
        radio.addEventListener('change', function handleCategoryChange() {
            document.querySelectorAll('.user-type-card').forEach((card) => {
                card.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
                card.classList.add('border-gray-200', 'text-gray-600');
            });
            const activeCard = document.querySelector(`.user-type-card[data-type="${this.dataset.typeName}"]`);
            if (activeCard) {
                activeCard.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
                activeCard.classList.remove('border-gray-200', 'text-gray-600');
            }

            const isInstitution = this.dataset.typeName === 'institution';
            const isPensionne = this.dataset.typeName === 'pensionne';

            document.getElementById('pension_code_container')?.classList.toggle('hidden', !isPensionne);
            const nameContainer = document.getElementById('name_container');
            const lastnameContainer = document.getElementById('lastname_container');
            if (nameContainer) {
                nameContainer.style.display = isInstitution ? 'block' : 'none';
            }
            if (lastnameContainer) {
                lastnameContainer.style.display = isInstitution ? 'none' : 'grid';
            }

            const nameInput = document.getElementById('name');
            if (nameInput) {
                nameInput.readOnly = !isInstitution;
                nameInput.placeholder = isInstitution ? "Nom de l'institution" : 'Pierre Rubens MILORME';
                if (!isInstitution) {
                    updateNameField();
                }
            }
        });
    });

    function updateNameField() {
        const selected = document.querySelector('input[name="primary_category"]:checked');
        if (selected?.dataset.typeName === 'institution') {
            return;
        }
        const last = document.getElementById('lastname')?.value.trim() ?? '';
        const first = document.getElementById('firstname')?.value.trim() ?? '';
        const name = document.getElementById('name');
        if (name) {
            name.value = [first, last].filter(Boolean).join(' ');
        }
    }

    document.getElementById('lastname')?.addEventListener('input', updateNameField);
    document.getElementById('firstname')?.addEventListener('input', updateNameField);
    document.querySelector('input[name="primary_category"]:checked')?.dispatchEvent(new Event('change'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAuthRegister);
} else {
    bootAuthRegister();
}
