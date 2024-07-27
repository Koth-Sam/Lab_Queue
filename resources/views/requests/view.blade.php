@extends('layouts.app')
@section('title', 'View Requests')
@section('content')

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex items-center mb-4">
        <h1 class="text-2xl font-bold mr-2">My Requests</h1>
        <a href="{{ route('requests.create') }}" class="text-black px-2 py-2 border border-black-800 border-solid p-4 rounded">
            Add Request
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-4">
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
                                <button 
                                    id="feedback-button-{{ $request->id }}" 
                                    class="text-black px-2 py-2 p-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" 
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
        <!-- Modal content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Rate and Comment Your Experience
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
                                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Submit</button>
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
            event.preventDefault(); // Prevent the default form submission

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
                    'X-CSRF-TOKEN': formData.get('_token') // Add CSRF token
                }
            })
            .then(response => response.json())
            .then(data => {
                closeFeedbackModal();
                // Optionally handle success message
                console.log('Feedback submitted successfully');
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

        document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 5000);
    
    });

</script>
@endsection
