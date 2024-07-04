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
                    <th class="py-2 px-4 border-b border-gray-200">Course Name</th>
                    <th class="py-2 px-4 border-b border-gray-200">Course Code</th>
                    <th class="py-2 px-4 border-b border-gray-200">Request Type</th>
                    <th class="py-2 px-4 border-b border-gray-200">Seat Number</th>
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
                            <form action="{{ route('ta.update', $request->id) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()">
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
