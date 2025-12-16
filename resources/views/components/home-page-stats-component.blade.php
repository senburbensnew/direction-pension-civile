<div class="grid md:grid-cols-3 gap-8">
    <!-- Graphique 1 : Bar Chart -->
    <div class="bg-gray-50 p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
        <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Pensions traitées / Année</h3>
        <canvas id="chartBar"></canvas>
    </div>
    <!-- Graphique 2 : Line Chart -->
    <div class="bg-gray-50 p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
        <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Évolution des demandes</h3>
        <canvas id="chartLine"></canvas>
    </div>
    <!-- Graphique 3 : Pie Chart -->
    <div class="bg-gray-50 p-6 rounded-2xl shadow-md hover:shadow-lg transition card-border-soft">
        <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Répartition des types de pensions</h3>
        <canvas id="chartPie"></canvas>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
        // === Bar Chart ===
        new Chart(document.getElementById('chartBar'), {
            type: 'bar',
            data: {
                labels: ['2020', '2021', '2022', '2023', '2024'],
                datasets: [{
                    label: 'Pensions traitées',
                    data: [3200, 4100, 3800, 4500, 4920],
                    backgroundColor: '#3b82f6'
                }]
            },
            options: { responsive: true }
        });

        // === Line Chart ===
        new Chart(document.getElementById('chartLine'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Demandes mensuelles',
                    data: [300, 420, 390, 500, 610, 580, 720],
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false
                }]
            },
            options: { responsive: true }
        });

        // === Pie Chart ===
        new Chart(document.getElementById('chartPie'), {
            type: 'pie',
            data: {
                labels: ['Retraite', 'Réversion', 'Invalidité'],
                datasets: [{
                    label: 'Répartition',
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#3b82f6', '#9333ea']
                }]
            },
            options: { responsive: true }
        });
</script>
