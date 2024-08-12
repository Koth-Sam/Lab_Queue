@extends('layouts.app')
@section('title', 'View All Requests')
@section('content')
<div class="bg-white p-4 rounded-lg shadow-md relative">
    <h1 class="text-2xl font-bold mb-4">All Requests</h1>

    <button wire:click="$emit('refreshRequests')" class="absolute top-5 right-4 p-2 bg-black-200 rounded-full shadow focus:outline-none">
        <i class="fas fa-sync-alt text-gray-600"></i>
    </button>       

    @livewire('requests-table', [
        'requests' => $requests,
        'uniqueCourses' => $uniqueCourses,
        'uniqueCourseCodes' => $uniqueCourseCodes,
        'uniqueRequestTypes' => $uniqueRequestTypes,
        'uniqueStatuses' => $uniqueStatuses,
        'uniqueTANames' => $uniqueTANames
        ]),
        
</div>

<script>
    function applyFilter(column) {
        const checkboxes = document.querySelectorAll('#filter-' + column + ' input[type="checkbox"]:checked');
        const selectedOptions = Array.from(checkboxes).map(checkbox => checkbox.value);
        const url = new URL(window.location.href);
        if (selectedOptions.length > 0) {
            url.searchParams.set(column, selectedOptions.join(','));
        } else {
            url.searchParams.delete(column);
        }
        window.location.href = url.toString();
    }

    function resetFilter(column) {
        const url = new URL(window.location.href);
        url.searchParams.delete(column);
        window.location.href = url.toString();
    }

    function sortTable(column, order) {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', column);
        url.searchParams.set('order', order);
        window.location.href = url.toString();
    }

    function toggleFilter(column) {
        const filterElement = document.getElementById('filter-' + column);
        filterElement.classList.toggle('hidden');
        event.stopPropagation(); // Stop the event from bubbling up to prevent triggering other events
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(event) {
            const filterDropdowns = document.querySelectorAll('.filter-dropdown');
            filterDropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    });

</script>

<style>
    .filter-dropdown {
        position: absolute;
        background-color: white;
        border: 1px solid #ddd;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .filter-dropdown div {
        display: flex;
        align-items: center;
        justify-content: flex-start; /* Left align items */
    }

    .filter-dropdown label {
        margin-left: 5px;
    }

    .filter-dropdown button {
        margin-top: 10px;
        border: 1px solid #ccc; /* Add border to buttons */
        padding: 5px 10px;
        background-color: white;
        cursor: pointer;
    }

    
</style>
@endsection
