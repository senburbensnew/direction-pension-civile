export function bootSimulateurCalcul() {
    // Variables globales pour stocker les résultats
            let currentResults = {
                moisCarriere: 0,
                moyenne: 0,
                totalMoisRetenus: 0,
                pension: 0,
                allEntries: [],
                top60: []
            };

            // Fonction pour mettre à jour la barre de progression
            function updateProgress(percent) {
                const progressFill = document.getElementById('progress-fill');
                const progressPercent = document.getElementById('progress-percent');

                progressFill.style.width = percent + '%';
                progressPercent.textContent = Math.round(percent) + '%';
            }

            // Fonction pour attacher les écouteurs d'événements à une ligne spécifique
            function attachEventListenersToRow(row) {
                const salaryInput = row.querySelector('.salary-input');
                const monthsInput = row.querySelector('.months-input');

                if (salaryInput) {
                    salaryInput.addEventListener('input', calculate);
                }
                if (monthsInput) {
                    monthsInput.addEventListener('input', calculate);
                }
            }

            // Fonction pour attacher les écouteurs d'événements à tous les inputs existants
            function attachEventListenersToAllInputs() {
                document.querySelectorAll('.salary-input, .months-input').forEach(input => {
                    input.addEventListener('input', calculate);
                });
            }

            // Ajout / suppression de lignes
            function addRow() {
                const table = document.getElementById('salaryTable');
                const index = table.querySelectorAll('tr').length + 1;
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-600">${index}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="relative">
                            <input type="number" step="0.01" class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none salary-input" placeholder="Ex: 30000">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 text-sm">HTG</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none months-input" placeholder="Ex: 12" value="12" min="1">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <button type="button" onclick="removeRow(this)" class="text-navy hover:text-orange-500 transition-colors" aria-label="Supprimer la ligne">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                table.appendChild(row);

                // Ajouter les écouteurs d'événements pour la nouvelle ligne
                attachEventListenersToRow(row);
                calculate();
            }

            function removeRow(btn) {
                const tr = btn.closest('tr');
                tr.remove();
                // Ré-indexer
                const rows = document.querySelectorAll('#salaryTable tr');
                rows.forEach((r, i) => r.children[0].textContent = i + 1);
                calculate();
            }

            function resetTable() {
                const tbody = document.getElementById('salaryTable');
                tbody.innerHTML = '';
                // une ligne vide
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap text-base text-gray-600">1</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="relative">
                            <input type="number" step="0.01" class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none salary-input" placeholder="Ex: 45700">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 text-sm">HTG</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="number" class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none months-input" placeholder="Ex: 12" value="12" min="1">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <button type="button" onclick="removeRow(this)" class="text-navy hover:text-orange-500 transition-colors" aria-label="Supprimer la ligne">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);

                // Réinitialiser le champ mois de carrière
                document.getElementById('nombre-mois-carriere').value = '360';

                // Attacher les écouteurs d'événements
                attachEventListenersToRow(row);
                calculate();
            }

            // Fonction de calcul : toutes les lignes + top 60 mois
            function calculate() {
                updateProgress(30);

                const nb_mois_carriere = parseFloat(document.getElementById('nombre-mois-carriere').value) || 0;
                const rows = document.querySelectorAll('#salaryTable tr');
                let entries = [];

                // Mettre à jour l'affichage des mois de carrière
                document.getElementById('moisCarriere').textContent =
                    nb_mois_carriere > 0 ? nb_mois_carriere.toLocaleString('fr-FR') + ' mois' : '—';

                rows.forEach(r => {
                    const salaireVal = parseFloat(r.querySelector('.salary-input')?.value);
                    const moisVal = parseInt(r.querySelector('.months-input')?.value);
                    if (!isNaN(salaireVal) && salaireVal > 0 && !isNaN(moisVal) && moisVal > 0) {
                        entries.push({ salaire: salaireVal, mois: moisVal });
                    }
                });

                updateProgress(60);

                // Mettre à jour le tableau de toutes les lignes
                const allEntriesTable = document.getElementById('allEntriesTable');
                allEntriesTable.innerHTML = '';
                entries.forEach((e, i) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `<td class="px-4 py-2 text-base">${i+1}</td>
                                    <td class="px-4 py-2 text-base">${Number(e.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                    <td class="px-4 py-2 text-base">${e.mois}</td>`;
                    allEntriesTable.appendChild(tr);
                });

                if (entries.length === 0 || nb_mois_carriere <= 0) {
                    document.getElementById('moyenne').textContent = '—';
                    document.getElementById('nbMoisRetenus').textContent = '—';
                    document.getElementById('pensionResult').textContent = '—';
                    document.getElementById('top60Table').innerHTML = '';
                    document.getElementById('resultArea').style.display = 'none';

                    currentResults = {
                        moisCarriere: nb_mois_carriere,
                        moyenne: 0,
                        totalMoisRetenus: 0,
                        pension: 0,
                        allEntries: [],
                        top60: []
                    };

                    updateProgress(0);
                    return;
                }

                // Trier par salaire décroissant
                entries.sort((a, b) => b.salaire - a.salaire);

                // Sélection des 60 meilleurs mois
                let moisRestants = 60;
                const top60 = [];
                let totalPondere = 0;
                let totalMoisRetenus = 0;

                for (let e of entries) {
                    if (moisRestants <= 0) break;
                    const moisPris = Math.min(e.mois, moisRestants);
                    const contribution = e.salaire * moisPris;
                    top60.push({
                        salaire: e.salaire,
                        moisPris,
                        contribution
                    });
                    totalPondere += contribution;
                    totalMoisRetenus += moisPris;
                    moisRestants -= moisPris;
                }

                // Calculs finaux
                const moyenne = totalPondere / totalMoisRetenus;
                const pension = (0.025 * moyenne * nb_mois_carriere) / 12;

                updateProgress(80);

                // Afficher top60 dans le tableau
                const top60Table = document.getElementById('top60Table');
                top60Table.innerHTML = '';
                top60.forEach((t, i) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `<td class="px-4 py-2 text-base">${i+1}</td>
                                    <td class="px-4 py-2 text-base">${Number(t.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                    <td class="px-4 py-2 text-base">${t.moisPris}</td>
                                    <td class="px-4 py-2 text-base">${Number(t.contribution).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>`;
                    top60Table.appendChild(tr);
                });

                // Mettre à jour affichage résumé
                document.getElementById('moyenne').textContent = Number(moyenne).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' HTG';
                document.getElementById('nbMoisRetenus').textContent = totalMoisRetenus;
                document.getElementById('pensionResult').textContent = Number(pension).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' HTG';

                // Afficher la section des résultats
                document.getElementById('resultArea').style.display = 'block';

                // Stocker les résultats pour l'export PDF
                currentResults = {
                    moisCarriere: nb_mois_carriere,
                    moyenne,
                    totalMoisRetenus,
                    pension,
                    allEntries: entries,
                    top60
                };

                updateProgress(100);
            }

            // Initialisation - S'assurer que le DOM est complètement chargé
            document.addEventListener('DOMContentLoaded', function() {
                // Attacher les écouteurs d'événements à tous les inputs existants
                attachEventListenersToAllInputs();

                // Ajouter les écouteurs d'événements pour le champ mois de carrière
                const moisCarriereInput = document.getElementById('nombre-mois-carriere');
                if (moisCarriereInput) {
                    // Utiliser plusieurs types d'événements pour s'assurer que ça fonctionne
                    moisCarriereInput.addEventListener('input', calculate);
                    moisCarriereInput.addEventListener('change', calculate);
                    moisCarriereInput.addEventListener('keyup', calculate);
                }

                // Calcul initial
                calculate();
            });

            // Ajouter également un écouteur d'événement global pour le champ mois de carrière
            // en dehors de DOMContentLoaded pour s'assurer qu'il est toujours disponible
            const moisCarriereInput = document.getElementById('nombre-mois-carriere');
            if (moisCarriereInput) {
                moisCarriereInput.addEventListener('input', calculate);
                moisCarriereInput.addEventListener('change', calculate);
                moisCarriereInput.addEventListener('keyup', calculate);
            }

            window.addRow = addRow;
            window.removeRow = removeRow;
            window.resetTable = resetTable;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSimulateurCalcul);
} else {
    bootSimulateurCalcul();
}
