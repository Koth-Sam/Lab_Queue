<!-- resources/views/requests/show.blade.php -->

@extends('layouts.app')
@section('title', 'View Request Details')
@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Request Details</h1>

    <div class="mb-4">
        <label class="block text-gray-700">Course Name:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->course_name }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Course Code:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->course_code }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Request Type:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->request_type }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Seat Number:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->seat_number }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Description:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->description }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700"> Subject Area/Lab Sheet:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->subject_area }}</p>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Screenshot(s):</label>
        @if ($request->screenshot)
            <img src="{{ asset('storage/' . $request->screenshot) }}" class="rounded">
        @else
            <p class="p-2 border border-gray-300 rounded">No screenshot provided</p>
        @endif
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Code URL:</label>
        @if ($request->code_url)
        <p class="p-2 border border-gray-300 rounded"><a href="{{ $request->code_url }}" target="_blank">{{ $request->code_url }}</a></p>
        @else
            <p class="p-2 border border-gray-300 rounded">No code URL provided</p>
        @endif
    </div>

    <div class="mb-4">
        <label class="block text-gray-700">Requested Date/Time:</label>
        <p class="p-2 border border-gray-300 rounded">{{ $request->requested_at }}</p>
    </div>

    <a href="{{ route('ta.index') }}" class="bg-black-500 text-black px-4 py-2 rounded hover:bg-blue-600">Back to List</a>
</div>
@endsection
