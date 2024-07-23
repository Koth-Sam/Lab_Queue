@extends('layouts.app')
@section('title', 'Submit Feedback')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Submit Feedback for Request #{{ $userRequest->id }}</h1>

    <form action="{{ route('feedback.store', $userRequest->id) }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="rating" class="block text-md font-medium mb-2">Rating</label>
            <select name="rating" id="rating" class="form-select block w-full p-2 border rounded">
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="comments" class="block text-md font-medium mb-2">Comments</label>
            <textarea name="comments" id="comments" rows="4" class="form-textarea block w-full p-2 border rounded"></textarea>
        </div>

        <button type="submit" class="bg-blue-500 text-white p-2 rounded">Submit Feedback</button>
    </form>
</div>
@endsection
