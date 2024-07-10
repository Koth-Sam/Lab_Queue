@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">All Requests</h1>

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
                    <th class="py-2 px-4 border-b border-gray-200">Status</th>
                    <th class="py-2 px-4 border-b border-gray-200">Requested Date/Time</th>
                    <th class="py-2 px-4 border-b border-gray-200">TA Name</th>
                    <th class="py-2 px-4 border-b border-gray-200">Accepted Date/Time</th>
                    <th class="py-2 px-4 border-b border-gray-200">Completed Date/Time</th>
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
                            <a href="{{ route('admin.show', $request->id) }}" class="text-blue-500 hover:text-blue-700">
                                View Details
                            </a>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <form action="{{ route('admin.update', $request->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="status" disabled="this.form.submit()" {{ $request->status == 'completed' ? 'disabled' : '' }}>
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
    @endif
</div>
@endsection
