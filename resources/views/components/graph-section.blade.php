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

<section class="py-12 bg-gray-50 fade-in">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-8 text-center gradient-text">
            Statistiques et Tendances
        </h2>

        <p class="text-gray-600 text-lg text-center mb-8 max-w-2xl mx-auto">
            Visualisez les données importantes concernant les pensions civiles et leur évolution
        </p>

        <!-- Graph Navigation Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-6">
            <button class="graph-tab active bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition-all text-sm" data-graph="pension-evolution">
                Évolution des Pensions
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-sm" data-graph="beneficiaires">
                Bénéficiaires
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-sm" data-graph="repartition">
                Répartition
            </button>
            <button class="graph-tab bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-lg font-semibold transition-all hover:bg-blue-50 text-sm" data-graph="performance">
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
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-xs">
                            <option>Annuel</option>
                            <option>Trimestriel</option>
                            <option>Mensuel</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="lineChartContainer">
                    <!-- D3 Line Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-xs text-gray-600">
                    <p>Source: Direction de la Pension Civile - Données actualisées en temps réel</p>
                </div>
            </div>

            <!-- Graph 2: Répartition des Bénéficiaires (Pie Chart) -->
            <div class="graph-content hidden" id="beneficiaires">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Répartition des Bénéficiaires par Catégorie</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-xs">
                            <option>2024</option>
                            <option>2023</option>
                            <option>2022</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="pieChartContainer">
                    <!-- D3 Pie Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-xs text-gray-600">
                    <p>Répartition par secteur d'activité et catégorie professionnelle</p>
                </div>
            </div>

            <!-- Graph 3: Répartition Géographique (Bar Chart) -->
            <div class="graph-content hidden" id="repartition">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Répartition Géographique des Bénéficiaires</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-xs">
                            <option>Par Région</option>
                            <option>Par Département</option>
                            <option>Par Ville</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="barChartContainer">
                    <!-- D3 Bar Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-xs text-gray-600">
                    <p>Distribution des bénéficiaires sur l'ensemble du territoire national</p>
                </div>
            </div>

            <!-- Graph 4: Performance de Traitement (Mixed Chart) -->
            <div class="graph-content hidden" id="performance">
                <div class="graph-header flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 lg:mb-0">Performance du Traitement des Dossiers</h3>
                    <div class="graph-controls flex gap-2">
                        <select class="graph-filter bg-gray-100 border-0 rounded-lg px-3 py-1 text-xs">
                            <option>Délais Moyens</option>
                            <option>Taux de Résolution</option>
                            <option>Volume Traité</option>
                        </select>
                        <button class="export-btn bg-blue-600 text-white px-3 py-1 rounded-lg text-xs hover:bg-blue-700">
                            <i class="fas fa-download mr-1"></i>Exporter
                        </button>
                    </div>
                </div>
                <div class="graph-container" id="mixedChartContainer">
                    <!-- D3 Mixed Chart will be rendered here -->
                </div>
                <div class="graph-footer mt-2 text-xs text-gray-600">
                    <p>Indicateurs de performance et délais de traitement moyens</p>
                </div>
            </div>
        </div>

        <!-- Mini Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-blue-600 mb-1">1,354</div>
                <div class="text-xs text-gray-600">Dossiers traités</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-green-600 mb-1">97%</div>
                <div class="text-xs text-gray-600">Taux de satisfaction</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-purple-600 mb-1">15j</div>
                <div class="text-xs text-gray-600">Délai moyen</div>
            </div>
            <div class="bg-white rounded-lg p-3 card-shadow text-center">
                <div class="text-xl font-bold text-orange-600 mb-1">680+</div>
                <div class="text-xs text-gray-600">Nouveaux bénéficiaires</div>
            </div>
        </div>
    </div>
</section>

<!-- D3.js Library -->
<script src="https://d3js.org/d3.v7.min.js"></script>

<script>
// Graph Tab Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.graph-tab');
    const graphContents = document.querySelectorAll('.graph-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetGraph = this.getAttribute('data-graph');

            // Update active tab
            tabs.forEach(t => t.classList.remove('active', 'bg-blue-600', 'text-white'));
            tabs.forEach(t => t.classList.add('bg-white', 'text-blue-600', 'border'));
            this.classList.remove('bg-white', 'text-blue-600', 'border');
            this.classList.add('active', 'bg-blue-600', 'text-white');

            // Show target graph
            graphContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            const targetContent = document.getElementById(targetGraph);
            targetContent.classList.remove('hidden');
            targetContent.classList.add('active');

            // Initialize the chart when tab is activated
            setTimeout(() => {
                switch(targetGraph) {
                    case 'pension-evolution':
                        createLineChart();
                        break;
                    case 'beneficiaires':
                        createPieChart();
                        break;
                    case 'repartition':
                        createBarChart();
                        break;
                    case 'performance':
                        createMixedChart();
                        break;
                }
            }, 100);
        });
    });

    // Initialize first chart
    setTimeout(() => {
        createLineChart();
    }, 500);

    // Sample data
    const pensionData = [
        { year: '2018', value: 1200 },
        { year: '2019', value: 1250 },
        { year: '2020', value: 1300 },
        { year: '2021', value: 1350 },
        { year: '2022', value: 1420 },
        { year: '2023', value: 1480 },
        { year: '2024', value: 1550 }
    ];

    const beneficiairesData = [
        { category: 'Fonctionnaires', value: 35 },
        { category: 'Enseignants', value: 25 },
        { category: 'Militaires', value: 15 },
        { category: 'Santé', value: 15 },
        { category: 'Administration', value: 10 }
    ];

    const repartitionData = [
        { region: 'Région Nord', value: 320 },
        { region: 'Région Sud', value: 280 },
        { region: 'Région Est', value: 240 },
        { region: 'Région Ouest', value: 310 },
        { region: 'Région Centre', value: 200 }
    ];

    const performanceData = [
        { month: 'Jan', dossiers: 120, delai: 20 },
        { month: 'Fév', dossiers: 150, delai: 18 },
        { month: 'Mar', dossiers: 180, delai: 15 },
        { month: 'Avr', dossiers: 90, delai: 25 },
        { month: 'Mai', dossiers: 200, delai: 12 },
        { month: 'Jun', dossiers: 170, delai: 14 }
    ];

    // Colors
    const colors = {
        blue: '#3b82f6',
        green: '#10b981',
        orange: '#f59e0b',
        red: '#ef4444',
        purple: '#8b5cf6',
        teal: '#14b8a6'
    };

    // Create Line Chart
    function createLineChart() {
        const container = document.getElementById('lineChartContainer');
        container.innerHTML = '';

        const margin = { top: 20, right: 30, bottom: 40, left: 50 };
        const width = container.clientWidth - margin.left - margin.right;
        const height = container.clientHeight - margin.top - margin.bottom;

        const svg = d3.select('#lineChartContainer')
            .append('svg')
            .attr('class', 'graph-svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        // Add gradient
        const gradient = svg.append('defs')
            .append('linearGradient')
            .attr('id', 'area-gradient')
            .attr('x1', '0%')
            .attr('y1', '0%')
            .attr('x2', '0%')
            .attr('y2', '100%');

        gradient.append('stop')
            .attr('offset', '0%')
            .attr('stop-color', colors.blue)
            .attr('stop-opacity', 0.3);

        gradient.append('stop')
            .attr('offset', '100%')
            .attr('stop-color', colors.blue)
            .attr('stop-opacity', 0);

        // Scales
        const x = d3.scalePoint()
            .domain(pensionData.map(d => d.year))
            .range([0, width])
            .padding(0.5);

        const y = d3.scaleLinear()
            .domain([1100, d3.max(pensionData, d => d.value) + 100])
            .range([height, 0])
            .nice();

        // Grid lines
        svg.append('g')
            .attr('class', 'grid')
            .call(d3.axisLeft(y)
                .tickSize(-width)
                .tickFormat('')
            );

        // Axes
        svg.append('g')
            .attr('class', 'axis')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));

        svg.append('g')
            .attr('class', 'axis')
            .call(d3.axisLeft(y));

        // Area
        const area = d3.area()
            .x(d => x(d.year))
            .y0(height)
            .y1(d => y(d.value))
            .curve(d3.curveMonotoneX);

        svg.append('path')
            .datum(pensionData)
            .attr('class', 'area')
            .attr('d', area);

        // Line
        const line = d3.line()
            .x(d => x(d.year))
            .y(d => y(d.value))
            .curve(d3.curveMonotoneX);

        svg.append('path')
            .datum(pensionData)
            .attr('class', 'line')
            .attr('stroke', colors.blue)
            .attr('d', line);

        // Dots
        svg.selectAll('.dot')
            .data(pensionData)
            .enter()
            .append('circle')
            .attr('class', 'dot')
            .attr('cx', d => x(d.year))
            .attr('cy', d => y(d.value))
            .attr('r', 4)
            .attr('fill', colors.blue)
            .attr('stroke', 'white')
            .attr('stroke-width', 2);

        // Tooltip
        const tooltip = d3.select('body').append('div')
            .attr('class', 'tooltip')
            .style('opacity', 0);

        // Add interactivity
        svg.selectAll('.dot')
            .on('mouseover', function(event, d) {
                tooltip.transition()
                    .duration(200)
                    .style('opacity', .9);
                tooltip.html(`<strong>${d.year}</strong><br/>${d.value} €`)
                    .style('left', (event.pageX + 10) + 'px')
                    .style('top', (event.pageY - 28) + 'px');
            })
            .on('mouseout', function(d) {
                tooltip.transition()
                    .duration(500)
                    .style('opacity', 0);
            });
    }

    // Create Pie Chart
    function createPieChart() {
        const container = document.getElementById('pieChartContainer');
        container.innerHTML = '';

        const margin = { top: 20, right: 20, bottom: 20, left: 20 };
        const width = container.clientWidth - margin.left - margin.right;
        const height = container.clientHeight - margin.top - margin.bottom;
        const radius = Math.min(width, height) / 2;

        const svg = d3.select('#pieChartContainer')
            .append('svg')
            .attr('class', 'graph-svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${width / 2 + margin.left},${height / 2 + margin.top})`);

        const color = d3.scaleOrdinal()
            .domain(beneficiairesData.map(d => d.category))
            .range([colors.blue, colors.green, colors.orange, colors.red, colors.purple]);

        const pie = d3.pie()
            .value(d => d.value)
            .sort(null);

        const arc = d3.arc()
            .innerRadius(0)
            .outerRadius(radius);

        const arcs = svg.selectAll('arc')
            .data(pie(beneficiairesData))
            .enter()
            .append('g')
            .attr('class', 'arc');

        // Add slices
        arcs.append('path')
            .attr('d', arc)
            .attr('fill', d => color(d.data.category))
            .attr('stroke', 'white')
            .attr('stroke-width', 2)
            .style('opacity', 0.8)
            .on('mouseover', function(event, d) {
                d3.select(this).transition()
                    .duration(200)
                    .attr('transform', 'scale(1.05)')
                    .style('opacity', 1);
            })
            .on('mouseout', function(event, d) {
                d3.select(this).transition()
                    .duration(200)
                    .attr('transform', 'scale(1)')
                    .style('opacity', 0.8);
            });

        // Add labels
        arcs.append('text')
            .attr('transform', d => `translate(${arc.centroid(d)})`)
            .attr('text-anchor', 'middle')
            .attr('class', 'pie-label')
            .text(d => `${d.data.value}%`)
            .style('font-weight', 'bold')
            .style('fill', 'white');

        // Legend
        const legend = svg.selectAll('.legend')
            .data(beneficiairesData)
            .enter()
            .append('g')
            .attr('class', 'legend')
            .attr('transform', (d, i) => `translate(${radius + 20},${(i * 20) - 40})`);

        legend.append('rect')
            .attr('width', 12)
            .attr('height', 12)
            .attr('fill', d => color(d.category));

        legend.append('text')
            .attr('x', 20)
            .attr('y', 10)
            .text(d => d.category)
            .style('font-size', '11px')
            .style('fill', '#374151');
    }

    // Create Bar Chart
    function createBarChart() {
        const container = document.getElementById('barChartContainer');
        container.innerHTML = '';

        const margin = { top: 20, right: 30, bottom: 40, left: 50 };
        const width = container.clientWidth - margin.left - margin.right;
        const height = container.clientHeight - margin.top - margin.bottom;

        const svg = d3.select('#barChartContainer')
            .append('svg')
            .attr('class', 'graph-svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        // Define a color scale for different bars
        const colorScale = d3.scaleOrdinal()
            .domain(repartitionData.map(d => d.region))
            .range([
                '#3b82f6', // Blue
                '#10b981', // Green
                '#f59e0b', // Orange
                '#ef4444', // Red
                '#8b5cf6'  // Purple
            ]);

        // Scales
        const x = d3.scaleBand()
            .domain(repartitionData.map(d => d.region))
            .range([0, width])
            .padding(0.2);

        const y = d3.scaleLinear()
            .domain([0, d3.max(repartitionData, d => d.value)])
            .range([height, 0])
            .nice();

        // Grid lines
        svg.append('g')
            .attr('class', 'grid')
            .call(d3.axisLeft(y)
                .tickSize(-width)
                .tickFormat('')
            );

        // Axes
        svg.append('g')
            .attr('class', 'axis')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x))
            .selectAll('text')
            .style('text-anchor', 'middle')
            .attr('dx', '0')
            .attr('dy', '.9em');

        svg.append('g')
            .attr('class', 'axis')
            .call(d3.axisLeft(y));

        // Bars with different colors
        svg.selectAll('.bar')
            .data(repartitionData)
            .enter()
            .append('rect')
            .attr('class', 'bar')
            .attr('x', d => x(d.region))
            .attr('y', d => y(d.value))
            .attr('width', x.bandwidth())
            .attr('height', d => height - y(d.value))
            .attr('fill', d => colorScale(d.region))
            .on('mouseover', function(event, d) {
                d3.select(this)
                    .transition()
                    .duration(200)
                    .attr('fill', d3.color(colorScale(d.region)).darker(0.3));
            })
            .on('mouseout', function(event, d) {
                d3.select(this)
                    .transition()
                    .duration(200)
                    .attr('fill', colorScale(d.region));
            });

        // Add values on bars
        svg.selectAll('.bar-label')
            .data(repartitionData)
            .enter()
            .append('text')
            .attr('class', 'bar-label')
            .attr('x', d => x(d.region) + x.bandwidth() / 2)
            .attr('y', d => y(d.value) - 5)
            .attr('text-anchor', 'middle')
            .text(d => d.value)
            .style('font-size', '11px')
            .style('fill', '#374151')
            .style('font-weight', 'bold');

        // Add legend for bar colors
        const legend = svg.append('g')
            .attr('transform', `translate(${width - 120}, 20)`);

        legend.selectAll('.bar-legend')
            .data(repartitionData)
            .enter()
            .append('g')
            .attr('class', 'bar-legend')
            .attr('transform', (d, i) => `translate(0, ${i * 20})`)
            .each(function(d, i) {
                const g = d3.select(this);

                g.append('rect')
                    .attr('width', 12)
                    .attr('height', 12)
                    .attr('fill', colorScale(d.region));

                g.append('text')
                    .attr('x', 20)
                    .attr('y', 10)
                    .text(d.region)
                    .style('font-size', '10px')
                    .style('fill', '#374151');
            });
    }

    // Create Mixed Chart (Bar + Line)
    function createMixedChart() {
        const container = document.getElementById('mixedChartContainer');
        container.innerHTML = '';

        const margin = { top: 20, right: 50, bottom: 40, left: 50 };
        const width = container.clientWidth - margin.left - margin.right;
        const height = container.clientHeight - margin.top - margin.bottom;

        const svg = d3.select('#mixedChartContainer')
            .append('svg')
            .attr('class', 'graph-svg')
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append('g')
            .attr('transform', `translate(${margin.left},${margin.top})`);

        // Scales
        const x = d3.scaleBand()
            .domain(performanceData.map(d => d.month))
            .range([0, width])
            .padding(0.2);

        const y0 = d3.scaleLinear()
            .domain([0, d3.max(performanceData, d => d.dossiers)])
            .range([height, 0])
            .nice();

        const y1 = d3.scaleLinear()
            .domain([0, d3.max(performanceData, d => d.delai)])
            .range([height, 0])
            .nice();

        // Grid lines
        svg.append('g')
            .attr('class', 'grid')
            .call(d3.axisLeft(y0)
                .tickSize(-width)
                .tickFormat('')
            );

        // Axes
        svg.append('g')
            .attr('class', 'axis')
            .attr('transform', `translate(0,${height})`)
            .call(d3.axisBottom(x));

        svg.append('g')
            .attr('class', 'axis')
            .call(d3.axisLeft(y0));

        svg.append('g')
            .attr('class', 'axis')
            .attr('transform', `translate(${width},0)`)
            .call(d3.axisRight(y1));

        // Bars (Dossiers traités)
        svg.selectAll('.bar-dossiers')
            .data(performanceData)
            .enter()
            .append('rect')
            .attr('class', 'bar bar-dossiers')
            .attr('x', d => x(d.month))
            .attr('y', d => y0(d.dossiers))
            .attr('width', x.bandwidth())
            .attr('height', d => height - y0(d.dossiers))
            .attr('fill', colors.blue);

        // Line (Délai moyen)
        const line = d3.line()
            .x(d => x(d.month) + x.bandwidth() / 2)
            .y(d => y1(d.delai))
            .curve(d3.curveMonotoneX);

        svg.append('path')
            .datum(performanceData)
            .attr('class', 'line')
            .attr('stroke', colors.red)
            .attr('stroke-width', 2.5)
            .attr('fill', 'none')
            .attr('d', line);

        // Dots for line
        svg.selectAll('.dot-delai')
            .data(performanceData)
            .enter()
            .append('circle')
            .attr('class', 'dot dot-delai')
            .attr('cx', d => x(d.month) + x.bandwidth() / 2)
            .attr('cy', d => y1(d.delai))
            .attr('r', 4)
            .attr('fill', colors.red)
            .attr('stroke', 'white')
            .attr('stroke-width', 2);

        // Legend
        const legend = svg.append('g')
            .attr('transform', `translate(${width - 100}, 20)`);

        legend.append('rect')
            .attr('width', 12)
            .attr('height', 12)
            .attr('fill', colors.blue);

        legend.append('text')
            .attr('x', 20)
            .attr('y', 10)
            .text('Dossiers traités')
            .style('font-size', '11px')
            .style('fill', '#374151');

        legend.append('rect')
            .attr('y', 15)
            .attr('width', 12)
            .attr('height', 2)
            .attr('fill', colors.red);

        legend.append('text')
            .attr('x', 20)
            .attr('y', 25)
            .text('Délai moyen (jours)')
            .style('font-size', '11px')
            .style('fill', '#374151');
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const activeTab = document.querySelector('.graph-tab.active');
            if (activeTab) {
                const targetGraph = activeTab.getAttribute('data-graph');
                switch(targetGraph) {
                    case 'pension-evolution':
                        createLineChart();
                        break;
                    case 'beneficiaires':
                        createPieChart();
                        break;
                    case 'repartition':
                        createBarChart();
                        break;
                    case 'performance':
                        createMixedChart();
                        break;
                }
            }
        }, 250);
    });
});
</script>
