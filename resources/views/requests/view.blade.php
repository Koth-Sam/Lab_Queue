@extends('layouts.app')
@section('title', 'View Requests')
@section('content')

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex items-center mb-4">
        <h1 class="text-2xl font-bold mr-2">My Requests</h1>
        <a href="{{ route('requests.create') }}" class="p-2 rounded font-bold" style="background-color: #023d80; color:#ffffff;">
            Add Request
        </a>
    </div>

    <div id="notification" 
     class="z-50 hidden text-white p-4 rounded-lg shadow-lg fixed top-10 right-5" 
     style="background-color: #002147; opacity: 1;">
    </div>


    @if(session('success'))
    <div id="success-message" class="p-4 rounded mb-4" style="background-color: #023d80; color: white;">
        {{ session('success') }}
    </div>
    @endif


    @if($requests->isEmpty())
        <p>No requests found.</p>
    @else
    <table class="min-w-full bg-white border-collapse shadow-lg">
        <thead>
            <tr>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="0">
                    Course<br> Name <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="1">
                    Course<br> Code <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="2">
                    Request Type <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="3">
                    Seat <br>Number <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="4">
                    Status <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="5">
                    Requested Date/Time <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="6">
                    TA Name <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="7">
                    Accepted Date/Time <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300 cursor-pointer hover:bg-gray-300 sortable" data-column="8">
                    Completed Date/Time <i class="fas fa-sort"></i>
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300">
                    Actions
                </th>
                <th class="py-1 px-4 bg-gray-200 text-[#002147] text-left text-sm uppercase font-semibold border-b border-gray-300">
                    
                </th>
            </tr>
        </thead>
        <tbody>
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
                        @if($request->accepted_at)
                            <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->accepted_at)->format('Y-m-d') }}</div>
                            <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->accepted_at)->format('H:i:s') }}</div>
                        @else
                            <span class="text-gray-500"> - </span>
                        @endif
                    </td>
                    
                    <td class="py-3 px-5 border-b border-gray-300">
                        @if($request->completed_at)
                            <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->completed_at)->format('Y-m-d') }}</div>
                            <div class="whitespace-nowrap">{{ \Carbon\Carbon::parse($request->completed_at)->format('H:i:s') }}</div>
                        @else
                            <span class="text-gray-500"> - </span>
                        @endif
                    </td>
                    
                    <td class="py-3 px-5 border-b border-gray-300">
                        <a href="{{ route('requests.show', $request->id) }}" class="text-blue-800 underline hover:text-blue-600">
                            View Details
                        </a>
                    </td>
                    <td class="py-3 px-5 border-b border-gray-300 text-center">
                        @if($request->status === 'completed')
                            <button 
                                id="feedback-button-{{ $request->id }}" 
                                class="text-white px-1 py-1 p-1 rounded disabled:opacity-50 disabled:cursor-not-allowed " 
                                style="background-color: #023d80;" 
                                onclick="openFeedbackModal({{ $request->id }})" 
                                @if($request->isFeedbackSubmitted()) disabled @endif>
                                Rate Your Experience
                            </button>
                        @else
                            <span class="text-gray-500"> - </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    
    @endif
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
    
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Rate and Comment about Your Experience
                        </h3>
                        <br>
                        <div class="mt-2">
                            <form id="feedbackForm" action="" method="POST">
                                @csrf
                                <input type="hidden" name="request_id" id="feedbackRequestId">
                                <div class="mb-4">
                                    <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                                    <select name="rating" id="rating" class="form-select mt-1 block w-full border rounded-md shadow-sm">
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="3">3 - Good</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="1">1 - Poor</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="comments" class="block text-sm font-medium text-gray-700">Comments</label>
                                    <textarea name="comments" id="comments" rows="4" class="form-textarea mt-1 block w-full border rounded-md shadow-sm"></textarea>
                                </div>
                                <div class="flex justify-start">
                                    <button type="button" onclick="closeFeedbackModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md mr-2">Cancel</button>
                                    <button type="submit" class="text-white px-4 py-2 rounded-md" style="background-color: #023d80;">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    document.addEventListener('DOMContentLoaded', function () {
        let successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.remove();
            }, 3500);
        }
        });

    function openFeedbackModal(requestId) 
    {
        document.getElementById('feedbackRequestId').value = requestId;
        document.getElementById('feedbackForm').action = `/requests/${requestId}/feedback`;
        document.getElementById('feedbackModal').classList.remove('hidden');
    }

    function closeFeedbackModal() {
        document.getElementById('feedbackModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('feedbackForm').addEventListener('submit', function(event) {
            event.preventDefault();

            var requestId = document.getElementById('feedbackRequestId').value;
            var feedbackButton = document.getElementById('feedback-button-' + requestId);

            if (feedbackButton) {
                feedbackButton.disabled = true;
                feedbackButton.setAttribute('data-submitted', 'true');
                feedbackButton.classList.add('opacity-50', 'cursor-not-allowed');
            }

            var formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            })
            .then(response => response.json())
            .then(data => {
                closeFeedbackModal();
            
            })
            .catch(error => console.error('Error submitting feedback:', error));
        });
    });

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
                this.sortOrder = 1;
            } else if (this.sortOrder === 1) {
                this.sortOrder = -1;
            } else {
                this.sortOrder = 0;
            }

            if (this.sortOrder === 0) {
              
                Array.from(tbody.querySelectorAll('tr'))
                    .sort((a, b) => new Date(b.children[5].innerText) - new Date(a.children[5].innerText))
                    .forEach(tr => tbody.appendChild(tr));

                th.querySelector('i').className = 'fas fa-sort';
                this.sortOrder = null;
            } else {
                Array.from(tbody.querySelectorAll('tr'))
                    .sort(comparer(columnIdx, this.sortOrder === 1))
                    .forEach(tr => tbody.appendChild(tr));

                document.querySelectorAll('th.sortable i').forEach(icon => {
                    icon.className = 'fas fa-sort';
                });

                if (this.sortOrder === 1) {
                    th.querySelector('i').className = 'fas fa-sort-up';
                } else {
                    th.querySelector('i').className = 'fas fa-sort-down';
                }
            }
        })));

        Echo.private('requests.{{ auth()->id() }}')
        .listen('RequestStatusUpdated', (e) => {
            let message = `Your request has been ${e.status} by ${e.ta_name}`;
            showNotification(message);
        });

        function showNotification(message) {
        let notification = document.getElementById('notification');
        notification.innerText = message;
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 10000);
            }

});

</script>
@endsection
