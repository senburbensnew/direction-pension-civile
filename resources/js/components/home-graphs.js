export async function initHomeGraphs() {
    const d3 = await import('d3');
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
}
