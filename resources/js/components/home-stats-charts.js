export async function initHomeStatsCharts() {
    const bar = document.getElementById('chartBar');
    if (!bar) {
        return;
    }

    const { Chart } = await import('chart.js/auto');

    new Chart(bar, {
        type: 'bar',
        data: {
            labels: ['2020', '2021', '2022', '2023', '2024'],
            datasets: [{
                label: 'Pensions traitées',
                data: [3200, 4100, 3800, 4500, 4920],
                backgroundColor: '#3b82f6',
            }],
        },
        options: { responsive: true },
    });

    const line = document.getElementById('chartLine');
    if (line) {
        new Chart(line, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Demandes mensuelles',
                    data: [300, 420, 390, 500, 610, 580, 720],
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                }],
            },
            options: { responsive: true },
        });
    }

    const pie = document.getElementById('chartPie');
    if (pie) {
        new Chart(pie, {
            type: 'pie',
            data: {
                labels: ['Retraite', 'Réversion', 'Invalidité'],
                datasets: [{
                    label: 'Répartition',
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#3b82f6', '#9333ea'],
                }],
            },
            options: { responsive: true },
        });
    }
}
