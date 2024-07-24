@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-4xl p-5 rounded-lg text-center bg-blue" style="min-height: 300px;">
        <div class="space-y-4">
            <a href="{{ route('requests.create') }}" class="block w-full max-w-2xl mx-auto bg-blue-1000 text-black border border-black-2000 border-width px-2 px-12 py-6 rounded-lg">
                Add a Request
            </a>
            <a href="{{ route('requests.index') }}" class="block w-full max-w-2xl mx-auto bg-black-1000 text-black border border-black-2000 px-12 py-6 rounded-lg mt-8">
                View Your Requests
            </a>
        </div>
    </div>
</div>
@endsection
