<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $requests = Request::orderBy('requested_at', 'desc')->get();
        return view('admin.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $request = Request::findOrFail($id);
        $request->screenshot = json_decode($request->screenshot);

        return view('admin.show', compact('request'));
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
    public function update(HTTPRequest $request, string $id)
    {
        //
        $userRequest = Request::findOrFail($id);
        $userRequest->status = $request->status;

        if ($request->status == 'In-Progress') {
            $userRequest->ta_id = Auth::id();
            $userRequest->accepted_at = now();
        } elseif ($request->status == 'Completed') {
            $userRequest->completed_at = now();
        }

        $userRequest->save();

        return redirect()->route('admin.index')->with('success', 'Request status updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
