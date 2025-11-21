@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Simulateur de calcul de pension</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#3b82f6',
                            secondary: '#1e40af',
                            accent: '#10b981',
                            dark: '#1f2937',
                            light: '#f8fafc'
                        }
                    }
                }
            }
        </script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #f0f9ff 0%, #e6f3ff 100%);
            }

            .glass-effect {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .card-shadow {
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            .gradient-bg {
                background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            }

            .gradient-text {
                background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .input-focus:focus {
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
                border-color: #3b82f6;
            }

            .animate-pulse-slow {
                animation: pulse 3s infinite;
            }

            .sticky-header {
                position: sticky;
                top: 0;
                z-index: 10;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }

            .progress-bar {
                height: 6px;
                background-color: #e5e7eb;
                border-radius: 3px;
                overflow: hidden;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #10b981, #3b82f6);
                transition: width 0.5s ease-in-out;
            }

            .result-card {
                transition: all 0.3s ease;
            }

            .result-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
            }

            .fade-in {
                animation: fadeIn 0.5s ease-in-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <header class="mb-8 text-center">
            <h1 class="text-4xl font-bold gradient-text mb-2">Simulateur de Pension</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Calculez votre pension de retraite en fonction de votre carrière professionnelle</p>
        </header>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl card-shadow overflow-hidden fade-in">
            <!-- Card Header -->
            <div class="gradient-bg text-white p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div>
                        <h2 class="text-2xl font-bold">Calcul de votre pension</h2>
                        <p class="text-blue-100 mt-1">Saisissez vos informations salariales pour obtenir une estimation</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="flex space-x-2">
                            <button onclick="exportToPDF()" class="bg-white text-primary px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors flex items-center">
                                <i class="fas fa-file-pdf mr-2"></i> Exporter PDF
                            </button>
                            <button onclick="resetTable()" class="bg-white text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-red-50 transition-colors flex items-center">
                                <i class="fas fa-redo mr-2"></i> Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <!-- Instructions -->
                <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-100">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-xl mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Comment utiliser ce simulateur :</strong> Saisissez vos salaires et le nombre de mois correspondants.
                                Le système calculera automatiquement les 60 meilleurs mois, la moyenne pondérée et votre pension estimée.
                                <span class="font-semibold block mt-1">Vous devez effectuer le calcul pour chaque carrière séparément.</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Career Months Input -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée totale de carrière</label>
                    <div class="flex items-center space-x-4">
                        <div class="relative flex-1 max-w-xs">
                            <input id="nombre-mois-carriere" type="number" step="0.01" class="w-full p-3 border border-gray-300 rounded-lg input-focus focus:outline-none" placeholder="Ex: 360" value="360" min="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        <span class="text-sm text-gray-500">mois</span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">Durée totale de votre carrière en mois (ex: 30 ans = 360 mois)</div>
                </div>

                <!-- Salary Table -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Saisie des salaires</h3>
                        <button onclick="addRow()" class="bg-accent text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Ajouter une ligne
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salaire (HTG)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre de mois</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="salaryTable" class="bg-white divide-y divide-gray-200">
                                <!-- Initial row -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">1</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="relative">
                                            <input type="number" step="0.01" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none salary-input" placeholder="Ex: 45700">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                <span class="text-gray-400">HTG</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="number" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none months-input" placeholder="Ex: 12" value="12" min="1">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <button onclick="removeRow(this)" class="text-red-500 hover:text-red-700 transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div class="mb-8">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Progression du calcul</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div id="progress-fill" class="progress-fill" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Results Section -->
                <div id="resultArea" class="mt-8 p-6 rounded-2xl border border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 fade-in" style="display: none;">
                    <div class="flex items-center mb-6">
                        <div class="bg-primary rounded-full p-2 mr-3">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Résultats de votre simulation</h3>
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="bg-white result-card rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-blue-100 rounded-lg p-3 mr-4">
                                    <i class="fas fa-calendar-alt text-primary text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Mois de carrière</p>
                                    <p id="moisCarriere" class="text-xl font-bold text-gray-800">—</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white result-card rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-green-100 rounded-lg p-3 mr-4">
                                    <i class="fas fa-calculator text-accent text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Moyenne pondérée</p>
                                    <p id="moyenne" class="text-xl font-bold text-gray-800">—</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white result-card rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-purple-100 rounded-lg p-3 mr-4">
                                    <i class="fas fa-chart-bar text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Mois retenus</p>
                                    <p id="nbMoisRetenus" class="text-xl font-bold text-gray-800">—</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white result-card rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center">
                                <div class="bg-yellow-100 rounded-lg p-3 mr-4">
                                    <i class="fas fa-coins text-yellow-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Pension estimée</p>
                                    <p id="pensionResult" class="text-xl font-bold text-primary">—</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Results -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- All Entries Table -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-list-alt text-primary mr-2"></i>
                                    Toutes les lignes saisies
                                </h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Salaire (HTG)</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mois</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allEntriesTable" class="divide-y divide-gray-200"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Top 60 Months Table -->
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                                    60 meilleurs mois (retenus)
                                </h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Salaire (HTG)</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mois</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contribution</th>
                                        </tr>
                                    </thead>
                                    <tbody id="top60Table" class="divide-y divide-gray-200"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${index}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="relative">
                        <input type="number" step="0.01" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none salary-input" placeholder="Ex: 30000">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-400">HTG</span>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <input type="number" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none months-input" placeholder="Ex: 12" value="12" min="1">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <button onclick="removeRow(this)" class="text-red-500 hover:text-red-700 transition-colors">
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
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">1</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="relative">
                        <input type="number" step="0.01" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none salary-input" placeholder="Ex: 45700">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-400">HTG</span>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <input type="number" class="w-full p-2 border border-gray-300 rounded input-focus focus:outline-none months-input" placeholder="Ex: 12" value="12" min="1">
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <button onclick="removeRow(this)" class="text-red-500 hover:text-red-700 transition-colors">
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
                tr.innerHTML = `<td class="px-4 py-2 text-sm">${i+1}</td>
                                <td class="px-4 py-2 text-sm">${Number(e.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                <td class="px-4 py-2 text-sm">${e.mois}</td>`;
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
                tr.innerHTML = `<td class="px-4 py-2 text-sm">${i+1}</td>
                                <td class="px-4 py-2 text-sm">${Number(t.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                                <td class="px-4 py-2 text-sm">${t.moisPris}</td>
                                <td class="px-4 py-2 text-sm">${Number(t.contribution).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>`;
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

        // Fonction d'export PDF
        function exportToPDF() {
            if (currentResults.allEntries.length === 0) {
                alert("Veuillez d'abord saisir des données et effectuer un calcul.");
                return;
            }

            alert("Fonctionnalité d'export PDF - Cette fonctionnalité sera implémentée avec les bibliothèques appropriées.");
            // Ici vous pouvez intégrer la logique d'export PDF avec html2canvas et jsPDF
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
