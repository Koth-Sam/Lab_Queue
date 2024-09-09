@extends('layouts.app')
@section('title', 'View All Requests')
@section('content')
<div class="bg-white p-4 rounded-lg shadow-md relative">

    <div class="flex items-center mb-4">
        <h1 class="text-2xl font-bold">All Requests</h1>
        <button id="refresh-btn" class=" ml-6 mb-0" style="color: #002147;" title="Refresh Page">
            <i id="refresh-icon" class="fas fa-sync-alt"></i>
        </button>        
    </div>    

    @if(session('success'))
    <div class="p-4 rounded mb-4" style="background-color: #002147; color: white;">
        {{ session('success') }}
    </div>
    @endif

    <div id="notification" 
     class="z-50 hidden text-white p-4 rounded-lg shadow-lg fixed top-10 right-5" 
     style="background-color: #002147; opacity: 1;">New request added!</div>  

    @if($requests->isEmpty())
        <p>No requests found.</p>
    @else
    <div id="request-list-container"> 
        <table class="min-w-full bg-white border-collapse shadow-lg">
            <thead>
                <tr>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer sortable" data-column="course_name" data-order="{{ request('sort') == 'course_name' && request('order') == 'asc' ? 'desc' : 'asc' }}">
                        Course<br>Name <i class="fas fa-sort" onclick="sortTable('course_name', '{{ request('sort') == 'course_name' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                        <i class="fas fa-filter cursor-pointer" onclick="toggleFilter('course_name')"></i>
                        <div id="filter-course_name" class="filter-dropdown hidden">
                            @foreach($uniqueCourses as $course)
                                <div>
                                    <input type="checkbox" id="course_name_{{ $course }}" value="{{ $course }}" {{ in_array($course, explode(',', request('course_name'))) ? 'checked' : '' }}>
                                    <label for="course_name_{{ $course }}">{{ $course }}</label>
                                </div>
                            @endforeach
                            <button class="filter-button" onclick="applyFilter('course_name')">Apply</button>
                            <button class="filter-button" onclick="resetFilter('course_name')">Reset</button>
                        </div>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer sortable" data-column="course_code" data-order="{{ request('sort') == 'course_code' && request('order') == 'asc' ? 'desc' : 'asc' }}">
                        Course<br>Code <i class="fas fa-sort" onclick="sortTable('course_code', '{{ request('sort') == 'course_code' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                        <i class="fas fa-filter cursor-pointer" onclick="toggleFilter('course_code')"></i>
                        <div id="filter-course_code" class="filter-dropdown hidden">
                            @foreach($uniqueCourseCodes as $code)
                                <div>
                                    <input type="checkbox" id="course_code_{{ $code }}" value="{{ $code }}" {{ in_array($code, explode(',', request('course_code'))) ? 'checked' : '' }}>
                                    <label for="course_code_{{ $code }}">{{ $code }}</label>
                                </div>
                            @endforeach
                            <button class="filter-button" onclick="applyFilter('course_code')">Apply</button>
                            <button class="filter-button" onclick="resetFilter('course_code')">Reset</button>
                        </div>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer sortable" data-column="request_type" data-order="{{ request('sort') == 'request_type' && request('order') == 'asc' ? 'desc' : 'asc' }}">
                        Request Type <i class="fas fa-sort" onclick="sortTable('request_type', '{{ request('sort') == 'request_type' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                        <i class="fas fa-filter cursor-pointer" onclick="toggleFilter('request_type')"></i>
                        <div id="filter-request_type" class="filter-dropdown hidden">
                            @foreach($uniqueRequestTypes as $type)
                                <div>
                                    <input type="checkbox" id="request_type_{{ $type }}" value="{{ $type }}" {{ in_array($type, explode(',', request('request_type'))) ? 'checked' : '' }}>
                                    <label for="request_type_{{ $type }}">{{ $type }}</label>
                                </div>
                            @endforeach
                            <button class="filter-button" onclick="applyFilter('request_type')">Apply</button>
                            <button class="filter-button" onclick="resetFilter('request_type')">Reset</button>
                        </div>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer" data-column="seat_number">
                        Seat<br>Number <i class="fas fa-sort" onclick="sortTable('seat_number', '{{ request('sort') == 'seat_number' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer sortable" data-column="status" data-order="{{ request('sort') == 'status' && request('order') == 'asc' ? 'desc' : 'asc' }}">
                        Status <i class="fas fa-sort" onclick="sortTable('status', '{{ request('sort') == 'status' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                        <i class="fas fa-filter cursor-pointer" onclick="toggleFilter('status')"></i>
                        <div id="filter-status" class="filter-dropdown hidden">
                            @foreach($uniqueStatuses as $status)
                                <div>
                                    <input type="checkbox" id="status_{{ $status }}" value="{{ $status }}" {{ in_array($status, explode(',', request('status'))) ? 'checked' : '' }}>
                                    <label for="status_{{ $status }}">{{ $status }}</label>
                                </div>
                            @endforeach
                            <button class="filter-button" onclick="applyFilter('status')">Apply</button>
                            <button class="filter-button" onclick="resetFilter('status')">Reset</button>
                        </div>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300" data-column="requested_at">
                        Requested Date/Time <i class="fas fa-sort" onclick="sortTable('requested_at', '{{ request('sort') == 'requested_at' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300" data-column="ta_name" data-order="{{ request('sort') == 'ta_name' && request('order') == 'asc' ? 'desc' : 'asc' }}">
                        TA Name <i class="fas fa-sort" onclick="sortTable('ta_name', '{{ request('sort') == 'ta_name' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                        <i class="fas fa-filter cursor-pointer" onclick="toggleFilter('ta_name')"></i>
                        <div id="filter-ta_name" class="filter-dropdown hidden">
                            <div>
                                <input type="checkbox" id="ta_name_NA" value="N/A" {{ in_array('N/A', explode(',', request('ta_name'))) ? 'checked' : '' }}>
                                <label for="ta_name_NA">N/A</label>
                            </div>
                            @foreach($uniqueTANames as $name)
                                <div>
                                    <input type="checkbox" id="ta_name_{{ $name }}" value="{{ $name }}" {{ in_array($name, explode(',', request('ta_name'))) ? 'checked' : '' }}>
                                    <label for="ta_name_{{ $name }}">{{ $name }}</label>
                                </div>
                            @endforeach
                            <button class="filter-button" onclick="applyFilter('ta_name')">Apply</button>
                            <button class="filter-button" onclick="resetFilter('ta_name')">Reset</button>
                        </div>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300" data-column="accepted_at">
                        Accepted Date/Time <i class="fas fa-sort" onclick="sortTable('accepted_at', '{{ request('sort') == 'accepted_at' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300" data-column="completed_at">
                        Completed Date/Time <i class="fas fa-sort" onclick="sortTable('completed_at', '{{ request('sort') == 'completed_at' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer sortable" data-column="waiting_time">
                        Waiting Time <i class="fas fa-sort" onclick="sortTable('waiting_time', '{{ request('sort') == 'waiting_time' && request('order') == 'asc' ? 'desc' : 'asc' }}')"></i>
                    </th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300">Actions</th>
                    <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300"></th>
                </tr>
            </thead>
            <tbody id="request-list">
                @foreach($requests as $request)
                    <tr class="hover:bg-gray-100 transition-colors duration-200">
                        <td class="py-3 px-5 border-b border-gray-300">{{ $request->course_name }}</td>
                        <td class="py-3 px-5 border-b border-gray-300">{{ $request->course_code }}</td>
                        <td class="py-3 px-5 border-b border-gray-300">{{ ucfirst($request->request_type) }}</td>
                        <td class="py-3 px-5 border-b border-gray-300">{{ $request->seat_number }}</td>
                        <td class="py-3 px-5 border-b border-gray-300">{{ ucfirst($request->status) }}</td>
                        <td class="py-3 px-5 border-b border-gray-300"><div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->requested_at )->format('Y-m-d') }}</div>
                            <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->requested_at)->format('H:i:s') }}</div></td>
                        <td class="py-3 px-5 border-b border-gray-300">{{ $request->ta ? $request->ta->name : 'N/A' }}</td>
                        <td class="py-3 px-5 border-b border-gray-300">
                            @if($request->accepted_at && $request->status === 'accepted' || $request->status === 'completed')
                                <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->accepted_at)->format('Y-m-d') }}</div>
                                <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->accepted_at)->format('H:i:s') }}</div>
                            @else
                                <span class="text-gray-500"> - </span>
                            @endif
                        </td>
           
                        <td class="py-3 px-5 border-b border-gray-300">
                            @if($request->completed_at && $request->status === 'completed')
                                <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->completed_at)->format('Y-m-d') }}</div>
                                <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->completed_at)->format('H:i:s') }}</div>
                            @else
                                <span class="text-gray-500"> - </span>
                            @endif
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            @if($request->accepted_at && $request->completed_at)
                                @php
                                    $accepted = \Carbon\Carbon::parse($request->accepted_at);
                                    $completed = \Carbon\Carbon::parse($request->completed_at);
                                    $waitingTime = $accepted->diffInMinutes($completed);
                                    $waitingTime = round($waitingTime);
                                @endphp
                                {{ $waitingTime }} mins.
                            @else
                                -
                            @endif
                        </td>     
                        <td class="py-3 px-5 border-b border-gray-300">
                            <a href="{{ route('ta.show', $request->id) }}" class="text-blue-800 underline hover:text-blue-600">
                                View Details
                            </a>
                        </td>
                        
                        <td class="py-3 px-5 border-b border-gray-300">
                            <form action="{{ route('ta.update', $request->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" {{ $request->status == 'completed' ? 'disabled' : '' }} class="block py-2 px-4 border rounded focus:outline-none focus:border-blue-500">
                                    <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="accepted" {{ $request->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="completed" {{ $request->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
        event.stopPropagation();
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

    document.addEventListener('DOMContentLoaded', function () {
            if (window.Echo) {
                window.Echo.channel('requests')
                    .listenToAll((e,data) => {
                        const notificationElement = document.getElementById('notification');
                        if (notificationElement) {
                            notificationElement.classList.remove('hidden');
                            setTimeout(() => {
                                notificationElement.classList.add('hidden');
                            }, 10000);
                        }
                    });
            } else {
                console.error('Laravel Echo is not initialized');
            }
        });

        document.getElementById('refresh-btn').addEventListener('click', function() {
            const refreshIcon = document.getElementById('refresh-icon');
            refreshIcon.classList.add('animate-spin'); 
    axios.get('{{ route('ta.refresh') }}')
        .then(function (response) {
          
            document.getElementById('request-list').innerHTML = '';

         
            response.data.requests.forEach(request => {
                let acceptedAt = request.accepted_at ? request.accepted_at : '<span class="text-gray-500"> - </span>';
                let completedAt = request.completed_at ? request.completed_at : '<span class="text-gray-500"> - </span>';
                let taName = request.ta && request.ta.name ? request.ta.name : 'N/A';

                let statusDropdown = `
                    <select name="status" class="block py-2 px-4 border rounded focus:outline-none focus:border-blue-500" ${request.status === 'completed' ? 'disabled' : ''}>
                        <option value="pending" ${request.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="accepted" ${request.status === 'accepted' ? 'selected' : ''}>Accepted</option>
                        <option value="completed" ${request.status === 'completed' ? 'selected' : ''}>Completed</option>
                    </select>
                `;

                let listItem = `
                    <tr class="hover:bg-gray-100 transition-colors duration-200">
                        <td class="py-3 px-5 border-b border-gray-300">${request.course_name}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${request.course_code}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${request.request_type}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${request.seat_number}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${request.status}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${request.requested_at ?? '<span class="text-gray-500"> - </span>'}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${taName}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${acceptedAt}</td>
                        <td class="py-3 px-5 border-b border-gray-300">${completedAt}</td>
                        <td class="py-3 px-5 border-b border-gray-300">
                            <a href="/ta/show/${request.id}" class="text-blue-800 underline hover:text-blue-600">View Details</a>
                        </td>
                        <td class="py-3 px-5 border-b border-gray-300">
                            <form action="/ta/update/${request.id}" method="POST">
                                @csrf
                                @method('PUT')
                                ${statusDropdown}
                            </form>
                        </td>
                    </tr>`;
                
                document.getElementById('request-list').innerHTML += listItem;
            });
        })
        .finally(function() {
            refreshIcon.classList.remove('animate-spin');
        })
        .catch(function (error) {
            console.log(error);
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
        justify-content: flex-start;
    }

    .filter-dropdown label {
        margin-left: 5px;
    }

    .filter-dropdown button {
        margin-top: 10px;
        border: 1px solid #ccc;
        padding: 5px 10px;
        background-color: white;
        cursor: pointer;
    }

</style>
@endsection
