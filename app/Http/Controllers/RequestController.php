<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;


class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $requests = Request::where('student_id', Auth::id())->get();
        return view('requests.view', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $request)
    {
        //
        $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255',
            'request_type' => 'required|in:assistance,sign-off',
            'seat_number' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_area' => 'required|string',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'code_url' => 'nullable|url',
        ]);

        $requestData = $request->all();
        $requestData['student_id'] = Auth::id();
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('screenshots', 'public');
            $requestData['screenshot'] = $path;
        }

        Request::create($requestData);

        return redirect()->route('requests.create')->with('success', 'Request submitted successfully.');
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
