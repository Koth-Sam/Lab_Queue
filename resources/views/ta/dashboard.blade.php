@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">TA Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Number of Requests Handled</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsHandledChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Chart: Number of Requests Handled by Status -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Number of Requests Handled by Request Status</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsHandledByStatusChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Chart: Number of Requests Handled by Request Type -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Number of Requests Handled by Request Type</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsHandledByRequestTypeChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Chart: Requests by Status -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Requests by Status</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsByStatusChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Chart: Number of Requests by Course -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Number of Requests Handled by Course</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsHandledByCourseChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Chart: Weekly Performance -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-96">
            <h2 class="text-xl font-semibold mb-4">Weekly Performance</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="weeklyPerformanceChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            function createChart(ctx, type, data, options) {
                return new Chart(ctx, {
                    type: type,
                    data: data,
                    options: options
                });
            }
    
            // Number of Requests Handled Chart
            const requestsHandledData = @json($requestsHandled);
            const handledLabels = requestsHandledData.map(data => data.date);
            const handledCounts = requestsHandledData.map(data => data.count);
    
            createChart(document.getElementById('requestsHandledChart').getContext('2d'), 'line', {
                labels: handledLabels,
                datasets: [{
                    label: 'Requests Handled',
                    data: handledCounts,
                    borderColor: 'rgba(75, 192, 192, 0.9)',
                    borderWidth: 1,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: false
                }]
            }, {
                responsive: true,
                maintainAspectRatio: false,
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
            });
    
            // Requests by Status Chart
            const requestsByStatusData = @json($requestsByStatus);
            const statusLabels = requestsByStatusData.map(data => data.status);
            const statusCounts = requestsByStatusData.map(data => data.count);
    
            createChart(document.getElementById('requestsByStatusChart').getContext('2d'), 'pie', {
                labels: statusLabels,
                datasets: [{
                    label: 'Requests by Status',
                    data: statusCounts,
                    backgroundColor: [
                        'rgba(252, 155, 62, 0.6)',
                        'rgba(45, 224, 42, 0.5)',
                        'rgba(247, 46, 46, 0.5)',
                    ],
                    borderColor: [
                        'rgba(252, 155, 62, 1)',
                        'rgba(45, 224, 42, 1)',
                        'rgba(247, 46, 46, 1)',
                    ],
                    borderWidth: 1
                }]
            }, {
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()}`;
                            }
                        }
                    }
                },
                aspectRatio: 1.5,
            });
    
            // Requests Handled by Request Type Chart
            const requestsHandledByRequestTypeData = @json($requestsHandledByRequestType);
            const requestTypeLabels = Array.from(new Set(requestsHandledByRequestTypeData.map(data => data.date)))
                .sort((a, b) => new Date(a) - new Date(b));
            const requestTypeData = requestTypeLabels.reduce((acc, date) => {
                acc[date] = {};
                return acc;
            }, {});

            requestsHandledByRequestTypeData.forEach(data => {
                if (!requestTypeData[data.date]) {
                    requestTypeData[data.date] = {};
                }
                requestTypeData[data.date][data.request_type] = data.count;
            });

        const uniqueRequestTypes = Array.from(new Set(requestsHandledByRequestTypeData.map(data => data.request_type)));

        
        const backgroundColors = ['rgba(153, 102, 255, 0.2)', 'rgba(28, 202, 66, 0.3)'];
        const borderColors = ['rgba(153, 102, 255, 1)', 'rgba(28, 202, 66, 1)'];

        const requestTypeDatasets = uniqueRequestTypes.map((requestType, index) => ({
            label: requestType,
            data: requestTypeLabels.map(date => requestTypeData[date]?.[requestType] || 0),
            backgroundColor: backgroundColors[index % 2],
            borderColor: borderColors[index % 2],
            borderWidth: 1,
            fill: false
    }));

    createChart(document.getElementById('requestsHandledByRequestTypeChart').getContext('2d'), 'line', {
        labels: requestTypeLabels,
        datasets: requestTypeDatasets
    }, {
        responsive: true,
        maintainAspectRatio: false,
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
    });

            // Requests Handled by Status Chart
            const requestsHandledByStatusData = @json($requestsHandledByStatus);
            const handledByStatusLabels = requestsHandledByStatusData.reduce((acc, cur) => {
                if (!acc.includes(cur.date)) {
                    acc.push(cur.date);
                }
                return acc;
            }, []);
    
            const statusDataByDate = requestsHandledByStatusData.reduce((acc, cur) => {
                if (!acc[cur.date]) {
                    acc[cur.date] = {};
                }
                acc[cur.date][cur.status] = cur.count;
                return acc;
            }, {});
    
            const uniqueStatuses = Object.keys(requestsHandledByStatusData.reduce((acc, cur) => {
                acc[cur.status] = true;
                return acc;
            }, {}));
    
            const statusDatasets = uniqueStatuses.map(status => ({
                label: status,
                data: handledByStatusLabels.map(date => statusDataByDate[date]?.[status] || 0),
                backgroundColor: status === 'completed' ? 'rgba(75, 192, 192, 0.2)' : 'rgba(153, 102, 255, 0.2)',
                borderColor: status === 'completed' ? 'rgba(75, 192, 192, 1)' : 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: false
            }));
    
            createChart(document.getElementById('requestsHandledByStatusChart').getContext('2d'), 'line', {
                labels: handledByStatusLabels,
                datasets: statusDatasets
            }, {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        },
                        stacked: true,
                        offset: true
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
                        },
                        stacked: true,
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
            });
    
            const requestsHandledByCourseData = @json($requestsHandledByCourse);
            const courseLabels = requestsHandledByCourseData.map(data => data.course_code);
            const courseCounts = requestsHandledByCourseData.map(data => data.count);

            new Chart(document.getElementById('requestsHandledByCourseChart'), {
                type: 'bar',
                data: {
                    labels: courseLabels,
                    datasets: [{
                        label: 'Number of Requests',
                        data: courseCounts,
                        backgroundColor: 'rgba(255, 159, 10, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Course'
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Number of Requests'
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
      
            // Weekly Performance Chart
            const weeklyPerformanceData = @json($weeklyPerformance);
            const weeklyLabels = weeklyPerformanceData.map(data => {
                const year = data.week.toString().substr(0, 4);
                const week = data.week.toString().substr(4);
                return `${year}-${week}`;
            });
    
            createChart(document.getElementById('weeklyPerformanceChart').getContext('2d'), 'bar', {
                labels: weeklyLabels,
                datasets: [{
                    label: 'Number of Requests',
                    data: weeklyPerformanceData.map(data => data.count),
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            }, {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Week'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
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
            });
        });

    </script>

@endsection
