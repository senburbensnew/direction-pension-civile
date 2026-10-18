export function initExistenceProof() {
    const form = document.querySelector('[data-dpc-existence-proof]');
    if (!form || form.dataset.existenceProofReady === '1') {
        return;
    }
    form.dataset.existenceProofReady = '1';

    let rowCount = Number(form.dataset.initialRowCount || 0);
    
        function addDependantRow() {
            const tbody = document.querySelector('#dependants-table tbody');
            const hiddenInput = tbody.querySelector('input[name="dependants"]');
            const messageDiv = document.getElementById('no-dependants-message');
            const table = document.getElementById('dependants-table');
    
            // Supprimer le champ caché si existant
            if (hiddenInput) hiddenInput.remove();
    
            const newIndex = rowCount;
            const newRow = document.createElement('tr');
            newRow.className = 'hover:bg-gray-50';
            newRow.innerHTML = `
                <td class="px-4 py-2 text-sm text-gray-700 align-center">${newIndex + 1}</td>
    
                <!-- Nom -->
                <td class="px-4 py-2 text-sm text-gray-900 align-top">
                <input type="text" 
                name="dependants[${newIndex}][nom]"
                class="w-full border rounded px-2 py-1">
                <div class="validation-message"></div>
                </td>
    
                <!-- Relation -->
                <td class="px-4 py-2 text-sm text-gray-700 align-top">
                <input type="text" 
                name="dependants[${newIndex}][relation]"
                class="w-full border rounded px-2 py-1">
                <div class="validation-message"></div>
                </td>
    
                <!-- Date de naissance -->
                <td class="px-4 py-2 text-sm text-gray-700 align-top">
                <input type="date"
                name="dependants[${newIndex}][date_naissance]"
                class="w-full border rounded px-2 py-1">
                <div class="validation-message"></div>
                </td>
    
                <!-- Sexe -->
                <td class="px-4 py-2 text-sm text-gray-700 align-top">
                <select name="dependants[${newIndex}][sexe_id]"
                    class="w-full border rounded px-2 py-1">
                <option value="">Sélectionner</option>
                ${document.getElementById('genderOptionsTemplate').innerHTML}
                </select>
                <div class="validation-message"></div>
                </td>
    
                <!-- Actions -->
                <td class="px-4 py-2 text-sm text-right space-x-2 align-center">
                </button>
                    <button type="button" onclick="deleteRow(this)" 
                        class="text-red-600 hover:text-red-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </td>
                `;
    
            tbody.appendChild(newRow);
            rowCount++;
    
            // Apply flatpickr to the new date input
            const newDateInput = newRow.querySelector('input[type="date"]');
            if (newDateInput && window.flatpickr) {
                flatpickr(newDateInput, {
                    locale:     document.documentElement.lang === 'en' ? 'default' : window.French,
                    dateFormat: 'Y-m-d',
                    altInput:   true,
                    altFormat:  document.documentElement.lang === 'en' ? 'm/d/Y' : 'd/m/Y',
                    allowInput: true,
                });
            }
    
            // Afficher le tableau
            table.hidden = false;
            messageDiv.hidden = true;
        }
    
        function deleteRow(button) {
            const row = button.closest('tr');
            const tbody = row.parentElement;
            row.remove();
    
            // Réindexer les lignes
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                // Mise à jour des noms de champs
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const fieldName = input.name.split('][')[1].replace(']', '');
                    input.name = `dependants[${index}][${fieldName}]`;
                });
    
                // Mise à jour du numéro de ligne
                row.querySelector('td:first-child').textContent = index + 1;
            });
    
            rowCount = rows.length;
    
            // Gérer l'affichage quand il n'y a plus de dépendants
            if (rowCount === 0) {
                document.getElementById('dependants-table').hidden = true;
                document.getElementById('no-dependants-message').hidden = false;
            }
        }
    
        // Validation avant soumission
        form.addEventListener('submit', function(e) {
            const dependants = document.querySelectorAll('[name^="dependants["]');
            if (dependants.length === 0) {
                e.preventDefault();
                alert('Veuillez ajouter au moins un dépendant.');
            }
        });
    window.addDependantRow = addDependantRow;
    window.deleteRow = deleteRow;
}
