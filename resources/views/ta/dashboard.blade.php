@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">TA Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Number of Requests Handled</h2>
            <canvas id="requestsHandledChart"></canvas>
        </div>

      
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-2">Requests by Status</h2>
            <canvas id="requestsByStatusChart"></canvas>
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
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Requests'
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
    });
</script>
@endsection
