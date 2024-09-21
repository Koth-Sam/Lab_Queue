<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\RequestAdded;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $requests = Request::where('student_id', Auth::id())->orderBy('requested_at', 'desc')->get();
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
            'course_code' => ['required', 'string','max:255', 'regex:/^[A-Za-z0-9\s\-]+$/',],
            'request_type' => 'required|in:assistance,sign-off',
            'seat_number' => ['required','string','max:255','regex:/^[A-Za-z0-9\s\-]+$/',],
            'description' => 'nullable|string',
            'subject_area' => 'required|string',
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'code_url' => 'nullable|url',
        
        ]);

        $requestData = $request->all();
        $requestData['student_id'] = Auth::id();
        $requestData['requested_at'] = now();

        if ($request->hasFile('screenshots')) {
            $screenshotPaths = [];
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('screenshots', 'public');
                $screenshotPaths[] = $path;
            }
            $requestData['screenshot'] = json_encode($screenshotPaths);
        } 
      
            Request::create($requestData);

        RequestAdded::dispatch("");

        return redirect()->route('requests.view')->with('success', 'Your request has been successfully submitted and added to our queue. You will be served shortly. Thank you for your patience.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $request = Request::findOrFail($id);
        $request->screenshot = json_decode($request->screenshot);
        return view('requests.show', compact('request'));
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
    public function update()
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

    public function studentHome()
    {
        return view('student.home');
    }

    

}
