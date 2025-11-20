@extends('layouts.main')

@section('title', 'Accueil')

@section('content')
<div class="max-w-5xl mx-auto p-6 bg-white rounded-lg shadow-md my-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Simulateur de calcul de pension</h2>
    </div>

    <div class="bg-white rounded-lg p-4 shadow-sm border">
<p class="text-sm text-gray-600 mb-4">
    Saisissez les lignes (Salaire + Nombre de mois). Le simulateur fera deux choses :
    <strong>1)</strong> afficher toutes les lignes saisies, et
    <strong>2)</strong> calculer les 60 meilleurs mois, la moyenne pondérée et la pension.
    <b>Vous devez effectuer le calcul pour chaque carrière séparément.</b>
</p>


        <!-- Tableau dynamique -->
        <div class="overflow-x-auto">
            <div class="py-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de mois de carrière</label>
                <div class="flex items-center space-x-4">
                    <input id="nombre-mois-carriere" type="number" step="0.01" class="w-48 p-2 border rounded"
                           placeholder="Ex: 360" value="360" min="0">
                    <span class="text-sm text-gray-500">mois</span>
                </div>
            </div>
            <table class="w-full table-auto mb-3">
                <thead>
                    <tr class="bg-gradient-to-r from-white to-blue-50 text-left">
                        <th class="px-3 py-2 border">#</th>
                        <th class="px-3 py-2 border">Salaire (HTG)</th>
                        <th class="px-3 py-2 border">Nombre de mois</th>
                        <th class="px-3 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody id="salaryTable" class="bg-white">
                    <!-- Ligne initiale -->
                    <tr>
                        <td class="px-3 py-2 border text-sm">1</td>
                        <td class="px-3 py-2 border">
                            <input type="number" step="0.01" class="w-full p-2 border rounded salary-input" placeholder="Ex: 45700">
                        </td>
                        <td class="px-3 py-2 border">
                            <input type="number" class="w-full p-2 border rounded months-input" placeholder="Ex: 12" value="12" min="1">
                        </td>
<td class="px-3 py-2 border text-center">
    <button onclick="removeRow(this)" class="text-red-500 hover:text-red-700">
        <!-- Icône poubelle SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3.5A1.5 1.5 0 002 5.5v1A1.5 1.5 0 003.5 8H4v9a2 2 0 002 2h8a2 2 0 002-2V8h.5A1.5 1.5 0 0018 6.5v-1A1.5 1.5 0 0016.5 4H15V3a1 1 0 00-1-1H6zm2 4a1 1 0 012 0v7a1 1 0 11-2 0V6zm4 0a1 1 0 012 0v7a1 1 0 11-2 0V6z" clip-rule="evenodd" />
        </svg>
    </button>
</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="addRow()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Ajouter</button>
            <button onclick="resetTable()" class="px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">Réinitialiser</button>
        </div>

        <!-- Résultats -->
        <div id="resultArea" class="mt-6 p-4 rounded shadow-inner" style="background: linear-gradient(90deg, #f8fafc, #f0f9ff); border: 1px solid #e6f0fb;">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Résultats</h3>

            <div class="grid md:grid-cols-4 gap-4 mb-4">
                <div class="bg-white p-3 rounded border">
                    <p class="text-sm text-gray-600">Mois de carrière</p>
                    <p id="moisCarriere" class="text-xl font-bold text-gray-800">—</p>
                </div>

                <div class="bg-white p-3 rounded border">
                    <p class="text-sm text-gray-600">Moyenne pondérée</p>
                    <p id="moyenne" class="text-xl font-bold text-gray-800">—</p>
                </div>

                <div class="bg-white p-3 rounded border">
                    <p class="text-sm text-gray-600">Nombre de mois retenus</p>
                    <p id="nbMoisRetenus" class="text-xl font-bold text-gray-800">—</p>
                </div>

                <div class="bg-white p-3 rounded border">
                    <p class="text-sm text-gray-600">Pension</p>
                    <p id="pensionResult" class="text-xl font-bold text-blue-700">—</p>
                </div>
            </div>

            <!-- Tableaux affichés pour l'export/impression -->
            <div id="exportContent">
                <!-- Toutes les lignes saisies -->
                <h4 class="font-semibold text-gray-800 mb-2">Toutes les lignes saisies</h4>
                <table class="w-full border-collapse mb-6">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 border text-left">#</th>
                            <th class="p-2 border text-left">Salaire (HTG)</th>
                            <th class="p-2 border text-left">Nombre de mois</th>
                        </tr>
                    </thead>
                    <tbody id="allEntriesTable"></tbody>
                </table>

                <!-- 60 meilleurs mois -->
                <h4 class="font-semibold text-gray-800 mb-2">60 meilleurs mois (retenus)</h4>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="p-2 border text-left">#</th>
                            <th class="p-2 border text-left">Salaire (HTG)</th>
                            <th class="p-2 border text-left">Mois retenus</th>
                            <th class="p-2 border text-left">Contribution (salaire × mois)</th>
                        </tr>
                    </thead>
                    <tbody id="top60Table"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bibliothèques pour génération PDF -->
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
        row.innerHTML = `
            <td class="px-3 py-2 border text-sm">${index}</td>
            <td class="px-3 py-2 border">
                <input type="number" step="0.01" class="w-full p-2 border rounded salary-input" placeholder="Ex: 30000">
            </td>
            <td class="px-3 py-2 border">
                <input type="number" class="w-full p-2 border rounded months-input" placeholder="Ex: 12" value="12" min="1">
            </td>
<td class="px-3 py-2 border text-center">
    <button onclick="removeRow(this)" class="text-red-500 hover:text-red-700">
        <!-- Icône poubelle SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3.5A1.5 1.5 0 002 5.5v1A1.5 1.5 0 003.5 8H4v9a2 2 0 002 2h8a2 2 0 002-2V8h.5A1.5 1.5 0 0018 6.5v-1A1.5 1.5 0 0016.5 4H15V3a1 1 0 00-1-1H6zm2 4a1 1 0 012 0v7a1 1 0 11-2 0V6zm4 0a1 1 0 012 0v7a1 1 0 11-2 0V6z" clip-rule="evenodd" />
        </svg>
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
        row.innerHTML = `
            <td class="px-3 py-2 border text-sm">1</td>
            <td class="px-3 py-2 border">
                <input type="number" step="0.01" class="w-full p-2 border rounded salary-input" placeholder="Ex: 45700">
            </td>
            <td class="px-3 py-2 border">
                <input type="number" class="w-full p-2 border rounded months-input" placeholder="Ex: 12" value="12" min="1">
            </td>
            <td class="px-3 py-2 border text-center">
                <button onclick="removeRow(this)" class="text-red-500 font-bold hover:text-red-700">Suppr</button>
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

        // Mettre à jour le tableau de toutes les lignes
        const allEntriesTable = document.getElementById('allEntriesTable');
        allEntriesTable.innerHTML = '';
        entries.forEach((e, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="p-2 border">${i+1}</td>
                            <td class="p-2 border">${Number(e.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                            <td class="p-2 border">${e.mois}</td>`;
            allEntriesTable.appendChild(tr);
        });

        if (entries.length === 0 || nb_mois_carriere <= 0) {
            document.getElementById('moyenne').textContent = '—';
            document.getElementById('nbMoisRetenus').textContent = '—';
            document.getElementById('pensionResult').textContent = '—';
            document.getElementById('top60Table').innerHTML = '';

            currentResults = {
                moisCarriere: nb_mois_carriere,
                moyenne: 0,
                totalMoisRetenus: 0,
                pension: 0,
                allEntries: [],
                top60: []
            };
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

        // Afficher top60 dans le tableau
        const top60Table = document.getElementById('top60Table');
        top60Table.innerHTML = '';
        top60.forEach((t, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td class="p-2 border">${i+1}</td>
                            <td class="p-2 border">${Number(t.salaire).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                            <td class="p-2 border">${t.moisPris}</td>
                            <td class="p-2 border">${Number(t.contribution).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>`;
            top60Table.appendChild(tr);
        });

        // Mettre à jour affichage résumé
        document.getElementById('moyenne').textContent = Number(moyenne).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' HTG';
        document.getElementById('nbMoisRetenus').textContent = totalMoisRetenus;
        document.getElementById('pensionResult').textContent = Number(pension).toLocaleString('fr-FR', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' HTG';

        // Stocker les résultats pour l'export PDF
        currentResults = {
            moisCarriere: nb_mois_carriere,
            moyenne,
            totalMoisRetenus,
            pension,
            allEntries: entries,
            top60
        };
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
