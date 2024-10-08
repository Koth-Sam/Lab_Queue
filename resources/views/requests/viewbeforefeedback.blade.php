@extends('layouts.app')
@section('title', 'View Requests')
@section('content')

<div class="bg-white p-6 rounded-lg shadow-md">
    
    <div class="flex items-center mb-4">
        
        <h1 class="text-2xl font-bold mr-2">My Requests</h1>
        <a href="{{ route('requests.create') }}" class=" text-black px-2 py-2 border border-black-800 border-solid p-4 rounded ">
            Add Request
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 rounded mb-4" style="background-color: #002147; color: white;">
        {{ session('success') }}
    </div>
    @endif


    @if($requests->isEmpty())
        <p>No requests found.</p>
    @else
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="0">
                        Course Name <i class="fas fa-sort"></i>
                    </th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="1">
                        Course Code <i class="fas fa-sort"></i>
                    </th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="2">
                        Request Type <i class="fas fa-sort"></i>
                    </th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="3">
                        Seat Number <i class="fas fa-sort"></i>
                    </th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="4">Status <i class="fas fa-sort"></i></th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="5">Requested Date/Time <i class="fas fa-sort"></i></th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="6">TA Name <i class="fas fa-sort"></i></th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="7">Accepted Date/Time <i class="fas fa-sort"></i></th>
                    <th class="py-2 px-4 border-b border-gray-200 cursor-pointer sortable" data-column="8">Completed Date/Time <i class="fas fa-sort"></i></th>
                    <th class="py-2 px-4 border-b border-gray-200">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->course_name }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->course_code }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->request_type }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->seat_number }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->status }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->requested_at }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->ta ? $request->ta->name : 'N/A' }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->accepted_at }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $request->completed_at }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <a href="{{ route('requests.show', $request->id) }}" class="text-blue-500 hover:text-blue-700">
                                View Details
                            </a>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            @if($request->status === 'completed')
                                <a href="{{ route('requests.view', $request->id) }}" class=" text-black px-2 py-2 p-4 rounded ">Rate Your Experience</a>
                            @else
                                <span class="text-gray-500"></span>
                            @endif
                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

        const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
            v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
            )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

        document.querySelectorAll('th.sortable').forEach(th => th.addEventListener('click', (function() {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const columnIdx = Array.from(th.parentNode.children).indexOf(th);

            if (this.sortOrder === undefined || this.sortOrder === null) {
                this.sortOrder = 1; // Ascending
            } else if (this.sortOrder === 1) {
                this.sortOrder = -1; // Descending
            } else {
                this.sortOrder = 0; // Default
            }

            if (this.sortOrder === 0) {
                // Default sort by requested date/time descending
                Array.from(tbody.querySelectorAll('tr'))
                    .sort((a, b) => new Date(b.children[5].innerText) - new Date(a.children[5].innerText))
                    .forEach(tr => tbody.appendChild(tr));

                th.querySelector('i').className = 'fas fa-sort';
                this.sortOrder = null;
            } else {
                Array.from(tbody.querySelectorAll('tr'))
                    .sort(comparer(columnIdx, this.sortOrder === 1))
                    .forEach(tr => tbody.appendChild(tr));

                // Reset other headers' icons
                document.querySelectorAll('th.sortable i').forEach(icon => {
                    icon.className = 'fas fa-sort';
                });

                // Update sorting icon
                if (this.sortOrder === 1) {
                    th.querySelector('i').className = 'fas fa-sort-up';
                } else {
                    th.querySelector('i').className = 'fas fa-sort-down';
                }
            }
        })));
    });
</script>
@endsection
