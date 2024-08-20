<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Feedback;
use App\Models\Request as UserRequest;
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
       /*  $userRequest = Request::findOrFail($id);

        // Check if the request is completed and if the current user is the owner
        if ($userRequest->status !== 'completed' || $userRequest->student_id !== Auth::id()) {
            return redirect()->route('requests.index')->with('error', 'Feedback can only be given for completed requests.');
        }

        return view('feedback.create', compact('userRequest')); */
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $request,$id)
    {
        //
        //$userRequest = Request::findOrFail($id);

        $validatedData = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
        ]);

        $userRequest = UserRequest::findOrFail($id);

        if ($userRequest->status !== 'completed' || $userRequest->student_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Feedback can only be given for completed requests.'], 403);
        }

        Feedback::create([
            'request_id' => $id,
            'student_id' => auth()->$userRequest->id,
            'rating' => $validatedData['rating'],
            'comments' => $validatedData['comments'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback submitted successfully.']);
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
    public function update(UserRequest $request, string $id)
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
