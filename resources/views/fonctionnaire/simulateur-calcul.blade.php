@extends('layouts.main')

@section('title', 'Simulateur de pension')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .input-focus:focus {
        box-shadow: 0 0 0 3px rgba(23, 48, 82, 0.15);
        border-color: #173052;
    }
    .progress-bar {
        height: 6px;
        background-color: #e5e7eb;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: #173052;
        transition: width 0.5s ease-in-out;
    }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Fonctionnaire</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Simulateur de Pension</h1>
            <p class="text-gray-700 max-w-2xl mx-auto">
                Calculez votre pension de retraite en fonction de votre carrière professionnelle.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calculator" aria-hidden="true"></i>
                </span>
                <div>
                    <h2 class="text-xl font-bold text-navy">Calcul de votre pension</h2>
                    <p class="text-base text-gray-700 mt-1">Saisissez vos informations salariales pour obtenir une estimation.</p>
                </div>
            </div>

            <div class="border border-orange-200 bg-orange-50 p-4 mb-6">
                <p class="text-sm font-bold text-orange-500 uppercase tracking-widest mb-1">Mode d’emploi</p>
                <p class="text-base text-gray-800 leading-relaxed">
                    <strong>Comment utiliser ce simulateur :</strong> saisissez vos salaires et le nombre de mois correspondants.
                    Le système retient les 60 meilleurs mois, calcule la moyenne pondérée et estime la pension.
                    <span class="font-semibold block mt-1">Effectuez le calcul pour chaque carrière séparément.</span>
                </p>
            </div>

            <div class="mb-6">
                <label for="nombre-mois-carriere" class="block text-base font-medium text-gray-700 mb-2">Durée totale de carrière</label>
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <input id="nombre-mois-carriere" type="number" step="0.01"
                               class="w-full p-3 border border-gray-200 input-focus focus:outline-none"
                               placeholder="Ex: 360" value="360" min="0">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                    </div>
                    <span class="text-base text-gray-700">mois</span>
                </div>
                <p class="mt-2 text-base text-gray-600">Durée totale de votre carrière en mois (ex. : 30 ans = 360 mois).</p>
            </div>

            <div class="mb-6 overflow-x-auto border border-gray-200">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-navy uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-navy uppercase tracking-wider">Salaire (HTG)</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-navy uppercase tracking-wider">Nombre de mois</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-navy uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody id="salaryTable" class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-base text-gray-600">1</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="relative">
                                    <input type="number" step="0.01"
                                           class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none salary-input"
                                           placeholder="Ex: 45700">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 text-sm">HTG</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <input type="number"
                                       class="w-full p-2.5 border border-gray-200 input-focus focus:outline-none months-input"
                                       placeholder="Ex: 12" value="12" min="1">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <button type="button" onclick="removeRow(this)" class="text-navy hover:text-orange-500 transition-colors" aria-label="Supprimer la ligne">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap justify-end mb-5 gap-3">
                <button type="button" onclick="resetTable()"
                        class="border border-gray-200 text-navy px-5 py-2.5 font-medium hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-redo mr-2"></i> Réinitialiser
                </button>
                <button type="button" onclick="addRow()"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 font-semibold transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Ajouter une ligne
                </button>
            </div>

            <div class="mb-8">
                <div class="flex justify-between text-base text-gray-700 mb-1">
                    <span>Progression du calcul</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="progress-bar">
                    <div id="progress-fill" class="progress-fill" style="width: 0%"></div>
                </div>
            </div>

            <div id="resultArea" class="mt-8 p-6 border border-gray-200 bg-gray-50" style="display: none;">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fas fa-chart-line" aria-hidden="true"></i>
                    </span>
                    <h3 class="text-xl font-bold text-navy">Résultats de votre simulation</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white p-4 border border-gray-200">
                        <p class="text-base text-gray-600">Mois de carrière</p>
                        <p id="moisCarriere" class="text-xl font-bold text-navy mt-1">—</p>
                    </div>
                    <div class="bg-white p-4 border border-gray-200">
                        <p class="text-base text-gray-600">Moyenne pondérée</p>
                        <p id="moyenne" class="text-xl font-bold text-navy mt-1">—</p>
                    </div>
                    <div class="bg-white p-4 border border-gray-200">
                        <p class="text-base text-gray-600">Mois retenus</p>
                        <p id="nbMoisRetenus" class="text-xl font-bold text-navy mt-1">—</p>
                    </div>
                    <div class="bg-white p-4 border border-gray-200">
                        <p class="text-sm font-bold text-orange-500 uppercase tracking-widest">Pension estimée</p>
                        <p id="pensionResult" class="text-xl font-bold text-navy mt-1">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h4 class="font-semibold text-navy">Toutes les lignes saisies</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">#</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">Salaire (HTG)</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">Mois</th>
                                    </tr>
                                </thead>
                                <tbody id="allEntriesTable" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <h4 class="font-semibold text-navy">60 meilleurs mois (retenus)</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">#</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">Salaire (HTG)</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">Mois</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-navy uppercase">Contribution</th>
                                    </tr>
                                </thead>
                                <tbody id="top60Table" class="divide-y divide-gray-200"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
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
    </script>
@endsection
