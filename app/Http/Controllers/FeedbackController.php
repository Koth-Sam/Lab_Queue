<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Feedback;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        //
        $userRequest = Request::findOrFail($id);

        // Check if the request is completed and if the current user is the owner
        if ($userRequest->status !== 'completed' || $userRequest->student_id !== Auth::id()) {
            return redirect()->route('requests.index')->with('error', 'Feedback can only be given for completed requests.');
        }

        return view('feedback.create', compact('userRequest'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $HTTPrequest,$id)
    {
        //
        $userRequest = Request::findOrFail($id);

        if ($userRequest->status !== 'completed' || $userRequest->student_id !== Auth::id()) {
            return redirect()->route('requests.index')->with('error', 'Feedback can only be given for completed requests.');
        }

        $validatedData = $HTTPrequest->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
        ]);

        Feedback::create([
            'request_id' => $userRequest->id,
            'student_id' => Auth::id(),
            'rating' => $validatedData['rating'],
            'comments' => $validatedData['comments'],
        ]);

        return redirect()->route('requests.index')->with('success', 'Feedback submitted successfully.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
