
@extends('layouts.app')
@section('title', 'Add a Request')
@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-4">Submit a Request</h1>

    @if(session('success'))
        <div class="bg-green-500 text-black p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif


    <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label for="course_name" class="block text-gray-700">Course Name</label>
            <input type="text" id="course_name" name="course_name" class="w-full p-2 border border-gray-300 rounded mt-1" required>
        </div>

        <div class="mb-4">
            <label for="course_code" class="block text-gray-700">Course Code</label>
            <input type="text" id="course_code" name="course_code" class="w-full p-2 border border-gray-300 rounded mt-1" required>
        </div>

        <div class="mb-4">
            <label for="request_type" class="block text-gray-700">Request Type</label>
            <select id="request_type" name="request_type" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                <option value="assistance">Assistance</option>
                <option value="sign-off">Sign-off</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="seat_number" class="block text-gray-700">Seat Number</label>
            <input type="text" id="seat_number" name="seat_number" class="w-full p-2 border border-gray-300 rounded mt-1" required>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700">Description</label>
            <textarea id="description" name="description" class="w-full p-2 border border-gray-300 rounded mt-1"></textarea>
        </div>

        <div class="mb-4">
            <label for="subject_area" class="block text-gray-700">Subject Area/Lab Sheet</label>
            <input type="text" id="subject_area" name="subject_area" class="w-full p-2 border border-gray-300 rounded mt-1" required>
        </div>


        <div class="mb-4">
            <label for="screenshot" class="block text-gray-700">Screenshot(s) (optional)</label>
            <input type="file" id="screenshot" name="screenshots[]" class="w-full p-2 border border-gray-300 rounded mt-1" multiple>
        </div>

        <div class="mb-4">
            <label for="code_url" class="block text-gray-700">Code URL (optional)</label>
            <input type="url" id="code_url" name="code_url" class="w-full p-2 border border-gray-300 rounded mt-1">
        </div>

        <button type="submit" class="bg-white text-black px-4 py-2 rounded border border-black ">
            Submit</button>
    </form>


</div>
@endsection
