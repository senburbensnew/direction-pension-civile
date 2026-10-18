<style>
    /* Graph Styles */
    .graph-tab {
        transition: all 0.3s ease;
    }

    .graph-tab.active {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        border: none;
    }

    .graph-content {
        transition: all 0.3s ease;
    }

    .graph-filter {
        outline: none;
        transition: all 0.3s ease;
    }

    .graph-filter:focus {
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .export-btn {
        transition: all 0.3s ease;
    }

    .export-btn:hover {
        transform: translateY(-1px);
    }

    /* D3 Graph Styles */
    .graph-container {
        height: 350px;
        position: relative;
    }

    .graph-svg {
        width: 100%;
        height: 100%;
    }

    .axis path,
    .axis line {
        fill: none;
        stroke: #e2e8f0;
        shape-rendering: crispEdges;
    }

    .axis text {
        font-size: 11px;
        fill: #64748b;
    }

    .grid line {
        stroke: #f1f5f9;
        stroke-width: 1;
        shape-rendering: crispEdges;
    }

    .line {
        fill: none;
        stroke-width: 2.5;
    }

    .area {
        fill: url(#area-gradient);
        opacity: 0.3;
    }

    .bar {
        transition: all 0.3s ease;
    }

    .bar:hover {
        opacity: 0.8;
    }

    .dot {
        transition: all 0.3s ease;
    }

    .dot:hover {
        r: 6;
        stroke-width: 3;
    }

    .tooltip {
        position: absolute;
        padding: 8px 12px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        border-radius: 6px;
        pointer-events: none;
        font-size: 12px;
        z-index: 100;
    }

    .legend {
        font-size: 11px;
    }

    .pie-label {
        font-size: 10px;
        fill: #374151;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .graph-tab {
            flex: 1;
            min-width: 140px;
            text-align: center;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }

        .graph-container {
            height: 300px;
        }

        .graph-controls {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    .graph-header {
        padding: 1rem 1.5rem 0.5rem;
    }

    .graph-footer {
        padding: 0.5rem 1.5rem 1rem;
    }
</style>

<section class="py-12 bg-gray-50 fade-in" data-dpc-home-graphs>
    <div class="site-gutter">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
            Statistiques et Tendances
        </h2>

        <p class="text-gray-600 text-lg text-center mb-8">
            Visualisez les données importantes concernant les pensions civiles et leur évolution
        </p>

        <!-- Graph Navigation Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-6">
            <button class="graph-tab active bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition-all text-base" data-graph="pension-evolution">
                Évolution des Pensions
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-base" data-graph="beneficiaires">
                Bénéficiaires
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-base" data-graph="repartition">
                Répartition
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-base" data-graph="performance">
                Performance
            </button>
        </div>

        <!-- Graph Container -->
        <div class="bg-white rounded-xl p-4 card-shadow">
            <!-- Graph 1: Évolution des Pensions (Line Chart) -->
            <div class="graph-content active" id="pension-evolution">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Évolution du Montant des Pensions (2018-2024)</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-sm">
                            <option>Annuel</option>
                            <option>Trimestriel</option>
                            <option>Mensuel</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="lineChartContainer">
                    <!-- D3 Line Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-sm text-gray-600">
                    <p>Source: Direction de la Pension Civile - Données actualisées en temps réel</p>
                </div>
            </div>

            <!-- Graph 2: Répartition des Bénéficiaires (Pie Chart) -->
            <div class="graph-content hidden" id="beneficiaires">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Répartition des Bénéficiaires par Catégorie</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-sm">
                            <option>2024</option>
                            <option>2023</option>
                            <option>2022</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="pieChartContainer">
                    <!-- D3 Pie Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-sm text-gray-600">
                    <p>Répartition par secteur d'activité et catégorie professionnelle</p>
                </div>
            </div>

            <!-- Graph 3: Répartition Géographique (Bar Chart) -->
            <div class="graph-content hidden" id="repartition">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Répartition Géographique des Bénéficiaires</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-sm">
                            <option>Par Région</option>
                            <option>Par Département</option>
                            <option>Par Ville</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="barChartContainer">
                    <!-- D3 Bar Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-sm text-gray-600">
                    <p>Distribution des bénéficiaires sur l'ensemble du territoire national</p>
                </div>
            </div>

            <!-- Graph 4: Performance de Traitement (Mixed Chart) -->
            <div class="graph-content hidden" id="performance">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Performance du Traitement des Dossiers</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-sm">
                            <option>Délais Moyens</option>
                            <option>Taux de Résolution</option>
                            <option>Volume Traité</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="mixedChartContainer">
                    <!-- D3 Mixed Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-sm text-gray-600">
                    <p>Indicateurs de performance et délais de traitement moyens</p>
                </div>
            </div>
        </div>

        <!-- Mini Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-blue-600 mb-1">1,354</div>
                <div class="text-sm text-gray-600">Dossiers traités</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-green-600 mb-1">97%</div>
                <div class="text-sm text-gray-600">Taux de satisfaction</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-purple-600 mb-1">15j</div>
                <div class="text-sm text-gray-600">Délai moyen</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-orange-600 mb-1">680+</div>
                <div class="text-sm text-gray-600">Nouveaux bénéficiaires</div>
            </div>
        </div>
    </div>
</section>

<!-- D3.js Library -->

