@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">TA Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Number of Requests Handled</h2>
            <canvas id="requestsHandledChart" class="mb-6"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests by Status</h2>
            <canvas id="requestsByStatusChart" class="mb-6"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Pending Requests</h2>
            <canvas id="pendingRequestsChart" class="mb-6"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md col-span-3">
            <h2 class="text-lg font-bold mb-2">Number of Requests Handled Per Day by Status</h2>
            <canvas id="requestsHandledByStatusChart" class="mb-6"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Average Response Time</h2>
            <canvas id="averageResponseTimeChart" class="mb-6"></canvas>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-md col-span-3">
            <h2 class="text-lg font-bold mb-2">Weekly Performance</h2>
            <canvas id="weeklyPerformanceChart" class="mb-6"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const requestsHandledData = @json($requestsHandled);
        const handledLabels = requestsHandledData.map(data => data.date);
        const handledCounts = requestsHandledData.map(data => data.count);

        new Chart(document.getElementById('requestsHandledChart'), {
            type: 'line',
            data: {
                labels: handledLabels,
                datasets: [{
                    label: 'Requests Handled',
                    data: handledCounts,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                        offset: true
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
                        },
                        min: 0, // Ensures the y-axis starts from zero
                        ticks: {
                            beginAtZero: true, // Ensures the y-axis starts from zero
                            stepSize: 1, // Ensures the y-axis steps through integers
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            }
        });

        const requestsByStatusData = @json($requestsByStatus);
        const statusLabels = requestsByStatusData.map(data => data.status);
        const statusCounts = requestsByStatusData.map(data => data.count);

        new Chart(document.getElementById('requestsByStatusChart'), {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Requests by Status',
                    data: statusCounts,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                }]
            },
            options: {
                responsive: true,
            }
        });

        const pendingRequestsData = @json($pendingRequests);
        const pendingLabels = pendingRequestsData.map(data => data.date);
        const pendingCounts = pendingRequestsData.map(data => data.count);

        new Chart(document.getElementById('pendingRequestsChart'), {
            type: 'line',
            data: {
                labels: pendingLabels,
                datasets: [{
                    label: 'Pending Requests',
                    data: pendingCounts,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                        offset: true 
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
                        },
                        min: 0, // Ensures the y-axis starts from zero
                        ticks: {
                            beginAtZero: true, // Ensures the y-axis starts from zero
                            stepSize: 1, // Ensures the y-axis steps through integers
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            }
        });

        const requestsHandledByStatusData = @json($requestsHandledByStatus);
        const statusDates = Array.from(new Set(requestsHandledByStatusData.map(data => data.date)));
        const acceptedCounts = statusDates.map(date => {
            const entry = requestsHandledByStatusData.find(data => data.date === date && data.status === 'accepted');
            return entry ? entry.count : 0;
        });
        const completedCounts = statusDates.map(date => {
            const entry = requestsHandledByStatusData.find(data => data.date === date && data.status === 'completed');
            return entry ? entry.count : 0;
        });

        new Chart(document.getElementById('requestsHandledByStatusChart'), {
            type: 'line',
            data: {
                labels: statusDates,
                datasets: [
                    {
                        label: 'Accepted Requests',
                        data: acceptedCounts,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    },
                    {
                        label: 'Completed Requests',
                        data: completedCounts,
                        borderColor: 'rgba(255, 206, 86, 1)',
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                        offset: true // Add space between the y-axis and the first data point
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
                        },
                        min: 0, // Ensures the y-axis starts from zero
                        ticks: {
                            beginAtZero: true, // Ensures the y-axis starts from zero
                            stepSize: 1, // Ensures the y-axis steps through integers
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            }
        });

        const averageResponseTimeData = @json($averageResponseTime);
        const responseTimeLabels = averageResponseTimeData.map(data => data.date);
        const responseTimeCounts = averageResponseTimeData.map(data => data.avg_response_time);

        new Chart(document.getElementById('averageResponseTimeChart'), {
            type: 'line',
            data: {
                labels: responseTimeLabels,
                datasets: [{
                    label: 'Average Response Time (hours)',
                    data: responseTimeCounts,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                        offset: true
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Average Response Time (hours)'
                        },
                        min: 0,
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
        const weeklyPerformanceData = @json($weeklyPerformance);
        const weeks = weeklyPerformanceData.map(data => data.week);
        const counts = weeklyPerformanceData.map(data => data.count);

        const weekLabels = weeks.map(week => {
            const year = Math.floor(week / 100);
            const weekNumber = week % 100;
            const startOfWeek = new Date(year, 0, (weekNumber - 1) * 7 + 1);
            const endOfWeek = new Date(year, 0, (weekNumber - 1) * 7 + 5);
            return `Week ${weekNumber} (${startOfWeek.toISOString().slice(0, 10)} - ${endOfWeek.toISOString().slice(0, 10)})`;
        });

        new Chart(document.getElementById('weeklyPerformanceChart'), {
            type: 'line',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Requests Handled',
                    data: counts,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Week'
                        },
                        offset: true // Add space between the y-axis and the first data point
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
                        },
                        min: 0, // Ensures the y-axis starts from zero
                        ticks: {
                            beginAtZero: true, // Ensures the y-axis starts from zero
                            stepSize: 1, // Ensures the y-axis steps through integers
                            callback: function(value) {
                                if (Number.isInteger(value)) {
                                    return value;
                                }
                            }
                        }
                    }
                }
            }
        });
    });
    });

</script>
@endsection
