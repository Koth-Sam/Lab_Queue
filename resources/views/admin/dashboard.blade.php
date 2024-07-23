@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>

    <!-- Summary Cards -->
    <div class="border border-black-400-bold rounded-lg p-6 mb-4">
        <h1 class="text-2xl font-bold mb-4">Summary</h1>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-2">Total Requests</h2>
                <p class="text-2xl">{{ $requestsSummary->total_requests }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-2">Pending Requests</h2>
                <p class="text-2xl">{{ $requestsSummary->pending_requests }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-2">Accepted Requests</h2>
                <p class="text-2xl">{{ $requestsSummary->accepted_requests }}</p>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-2">Completed Requests</h2>
                <p class="text-2xl">{{ $requestsSummary->completed_requests }}</p>
            </div>
        </div>
    </div>

    <!-- Widgets in two columns -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-6">
        <!-- Requests Handled by TA Widget -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests Handled by TA by Subject</h2>

            <!-- Dropdown for Course Selection -->
            <div class="mb-4">
                <label for="courseSelect" class="block text-md font-medium mb-2">Course</label>
                <select id="courseSelect" class="form-select block w-full p-2 border rounded" style="width: 320px; height: 38px;">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>

            <div class="flex justify-center items-center">
                <canvas id="requestsHandledByTAChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Weekly Performance Stacked Bar Chart -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Weekly Performance of TAs</h2>

            <!-- Dropdown for Course Selection -->
            <div class="mb-4">
                <label for="weeklyPerformanceCourseSelect" class="block text-md font-medium mb-2">Course</label>
                <select id="weeklyPerformanceCourseSelect" class="form-select block w-full p-2 border rounded" style="width: 320px; height: 38px;">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>

            <div class="flex justify-center items-center">
                <canvas id="weeklyPerformanceChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Requests Handled by TA by Request Type by Course Bar Chart -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests Handled by TA by Request Type by Course </h2>

            <!-- Dropdown for Course Selection -->
            <div class="mb-4">
                <label for="TAPerformanceByTypeCourseSelect" class="block text-md font-medium mb-2">Course</label>
                <select id="TAPerformanceByTypeCourseSelect" class="form-select block w-full p-2 border rounded" style="width: 320px; height: 38px;">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>

            <div class="flex justify-center items-center">
                <canvas id="TAPerformanceByTypeCourseChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Requests Handled by TA by Request Type -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests Handled by TA by Request Type</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsByTAAndTypeChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Average Response Time by TA -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Average Response Time by TA</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="averageResponseTimeByTAChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Requests by Course -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests by Course</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsByCourseChart" width="320" height="320"></canvas>
            </div>
        </div>

        <!-- Requests Trend Over Time -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests Trend Over Time</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsTrendChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- New Requests Trend Over Time by Type -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests Trend Over Time by Type</h2>
            <div class="flex-grow flex justify-center items-center">
                <canvas id="requestsTrendByTypeChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseSelect = document.getElementById('courseSelect');
    const weeklyPerformanceCourseSelect = document.getElementById('weeklyPerformanceCourseSelect');
    const TAPerformanceByTypeCourseSelect = document.getElementById('TAPerformanceByTypeCourseSelect');

    let requestsHandledByTAChartInstance;
    let weeklyPerformanceChart;
    let TAPerformanceByTypeCourseChart;

    function updateRequestsHandledByTAChart(courseName) {
        fetch(`/api/requests-handled-by-ta?course_name=${courseName}`)
            .then(response => response.json())
            .then(data => {
                const taLabels = data.map(item => item.ta);
                const taCounts = data.map(item => item.count);

                if (requestsHandledByTAChartInstance) {
                    requestsHandledByTAChartInstance.data.labels = taLabels;
                    requestsHandledByTAChartInstance.data.datasets[0].data = taCounts;
                    requestsHandledByTAChartInstance.update();
                } else {
                    requestsHandledByTAChartInstance = new Chart(document.getElementById('requestsHandledByTAChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: taLabels,
                            datasets: [{
                                label: 'Number of Requests',
                                data: taCounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'TA'
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
                                            return Number.isInteger(value) ? value : '';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
    }

    function updateWeeklyPerformanceChart(courseName) {
        fetch(`/api/get-weekly-performance?course_name=${courseName}`)
            .then(response => response.json())
            .then(data => {
                const weeks = [...new Set(data.map(item => `Week ${item.week}`))];
                const taNames = [...new Set(data.map(item => item.ta))];
                const datasets = taNames.map(ta => {
                    const taData = data.filter(item => item.ta === ta);
                    return {
                        label: ta,
                        data: weeks.map(week => {
                            const entry = taData.find(d => `Week ${d.week}` === week);
                            return entry ? entry.count : 0;
                        }),
                        backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.2)`,
                        borderColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 1)`,
                        borderWidth: 1,
                    };
                });

                weeklyPerformanceChart.data.labels = weeks;
                weeklyPerformanceChart.data.datasets = datasets;
                weeklyPerformanceChart.update();
            });
    }

    function fetchCoursesForElement(selectElement, updateChartCallback) {
        // Clear existing options to prevent duplicates
        selectElement.innerHTML = '';

        fetch('/api/courses')
            .then(response => response.json())
            .then(courses => {
                courses.sort();
                courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    selectElement.appendChild(option);
                });

                // Initialize the chart with the first course
                if (courses.length > 0) {
                    updateChartCallback(courses[0]);
                }
            });
    }

    // Initialize charts and course dropdowns
    fetchCoursesForElement(courseSelect, updateRequestsHandledByTAChart);
    fetchCoursesForElement(weeklyPerformanceCourseSelect, updateWeeklyPerformanceChart);
    fetchCoursesForElement(TAPerformanceByTypeCourseSelect, updateTAPerformanceByTypeCourseChart);

    courseSelect.addEventListener('change', function () {
        updateRequestsHandledByTAChart(this.value);
    });

    weeklyPerformanceCourseSelect.addEventListener('change', function () {
        updateWeeklyPerformanceChart(this.value);
    });

    TAPerformanceByTypeCourseSelect.addEventListener('change', function () {
        updateTAPerformanceByTypeCourseChart(this.value);
    });

    const weeklyPerformanceChartCtx = document.getElementById('weeklyPerformanceChart').getContext('2d');
    weeklyPerformanceChart = new Chart(weeklyPerformanceChartCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Week'
                    }
                },
                y: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Number of Requests'
                    },
                    min: 0,
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });

    function updateRequestsByTAAndTypeChart() {
        fetch('/api/requests-by-ta-and-type')
            .then(response => response.json())
            .then(data => {
                const taLabels = data.map(item => item.ta);
                const assistanceCounts = data.map(item => item.assistance);
                const signOffCounts = data.map(item => item['sign-off']);

                new Chart(document.getElementById('requestsByTAAndTypeChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: taLabels,
                        datasets: [
                            {
                                label: 'Assistance Requests',
                                data: assistanceCounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                            },
                            {
                                label: 'Sign-Off Requests',
                                data: signOffCounts,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'TA'
                                },
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Requests'
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(value) {
                                        return Number.isInteger(value) ? value : '';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
    }

    updateRequestsByTAAndTypeChart();

    function updateTAPerformanceByTypeCourseChart(courseName) {
        fetch(`/api/requests-handled-by-ta-by-course?course_name=${courseName}`)
            .then(response => response.json())
            .then(data => {
                const taLabels = data.map(item => item.ta);
                const assistanceCounts = data.map(item => item.assistance);
                const signOffCounts = data.map(item => item['sign-off']);

                if (TAPerformanceByTypeCourseChart) {
                    TAPerformanceByTypeCourseChart.destroy();
                }

                TAPerformanceByTypeCourseChart = new Chart(document.getElementById('TAPerformanceByTypeCourseChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: taLabels,
                        datasets: [
                            {
                                label: 'Assistance Requests',
                                data: assistanceCounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                            },
                            {
                                label: 'Sign-Off Requests',
                                data: signOffCounts,
                                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                borderColor: 'rgba(153, 102, 255, 1)',
                                borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'TA'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Requests'
                                },
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    callback: function (value) {
                                        return Number.isInteger(value) ? value : '';
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y;
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
    }

    // Average Response Time by TA
    const averageResponseTimeByTAData = @json($averageResponseTimeByTA);
    const taResponseTimeLabels = averageResponseTimeByTAData.map(data => data.ta ? data.ta.name : 'N/A');
    const taResponseTimeCounts = averageResponseTimeByTAData.map(data => data.avg_response_time);

    new Chart(document.getElementById('averageResponseTimeByTAChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: taResponseTimeLabels,
            datasets: [{
                label: 'Average Response Time (mins)',
                data: taResponseTimeCounts,
                backgroundColor: 'rgba(153, 102, 255, 0.2)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'TA'
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Avg Response Time (mins)'
                    },
                    min: 0,
                    ticks: {
                        beginAtZero: true,
                        stepSize: 10,
                        callback: function(value) {
                            return value.toFixed(0);
                        }
                    }
                }
            }
        }
    });

    // Requests by Course
    const requestsByCourseData = @json($requestsByCourse);
    const courseLabels = requestsByCourseData.map(data => data.course_name);
    const courseCounts = requestsByCourseData.map(data => data.count);

    new Chart(document.getElementById('requestsByCourseChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: courseLabels,
            datasets: [{
                label: 'Requests by Course',
                data: courseCounts,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
            }
        },
        aspectRatio: 0.5,
    });

    
    // Requests Trend Over Time
    const requestsTrendData = @json($requestsTrend);
    const trendLabels = requestsTrendData.map(data => data.date);
    const trendCounts = requestsTrendData.map(data => data.count);

    new Chart(document.getElementById('requestsTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Number of Requests',
                data: trendCounts,
                borderColor: 'rgba(255, 159, 64, 1)',
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
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
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });

    // New Requests Trend Over Time by Type
    const requestsTrendByTypeData = @json($requestsTrendByType);
    const trendLabelsByType = [...new Set(requestsTrendByTypeData.map(data => data.date))];
    const requestTypes = [...new Set(requestsTrendByTypeData.map(data => data.request_type))];

    const datasetsByType = requestTypes.map(type => {
        const dataForType = requestsTrendByTypeData.filter(data => data.request_type === type);
        return {
            label: type,
            data: trendLabelsByType.map(date => {
                const entry = dataForType.find(d => d.date === date);
                return entry ? entry.count : 0;
            }),
            borderColor: type === 'assistance' ? 'rgba(75, 192, 192, 1)' : 'rgba(153, 102, 255, 1)',
            backgroundColor: type === 'assistance' ? 'rgba(75, 192, 192, 0.2)' : 'rgba(153, 102, 255, 0.2)',
            fill: false
        };
    });

    new Chart(document.getElementById('requestsTrendByTypeChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabelsByType,
            datasets: datasetsByType,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
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
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });

});
</script>

@endsection
