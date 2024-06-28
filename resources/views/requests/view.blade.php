<!-- resources/views/requests/index.blade.php -->

@extends('layouts.app')
@section('title', 'Request History')
@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">My Requests</h1>

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
                    <th class="py-2 px-4 border-b border-gray-200"></th>
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
                        <td class="py-2 px-4 border-b border-gray-200">
                            <a href="{{ route('requests.show', $request->id) }}" class="text-blue-500 hover:text-blue-700">
                                View Details
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
