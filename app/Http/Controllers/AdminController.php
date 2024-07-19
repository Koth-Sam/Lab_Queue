<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as UserRequest;

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
    public function update(Request $request, string $id)
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

    public function dashboard()
    {
   
        $taId = Auth::id();

        $requestsHandled = UserRequest::where('ta_id', $taId)
            ->selectRaw('DATE(requested_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    
        $requestsByStatus = UserRequest::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->where('ta_id', $taId)
            ->get();
        
        $requestsHandledByRequestType = UserRequest::where('ta_id', $taId)
            ->selectRaw('DATE(requested_at) as date, request_type, count(*) as count')
            ->groupBy('date', 'request_type')
            ->orderBy('date', 'asc')
            ->get();

        
        $requestsHandledByStatus = UserRequest::where('ta_id', $taId)
            ->selectRaw('DATE(requested_at) as date, status, count(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date', 'asc')
            ->get();
    
        $averageResponseTime = UserRequest::where('ta_id', $taId)
            ->whereNotNull('completed_at')
            ->selectRaw('YEARWEEK(completed_at, 3) as week, request_type, 
                AVG(TIMESTAMPDIFF(MINUTE, accepted_at, completed_at)) as avg_response_time_minutes')
            ->groupBy('week', 'request_type')
            ->orderBy('week', 'asc')
            ->get();
    
        $weeklyPerformance = UserRequest::where('ta_id', $taId)
            ->selectRaw('YEARWEEK(requested_at, 3) as week, count(*) as count')
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();
  
        return view('ta.dashboard', compact(
            'requestsHandled',
            'requestsByStatus',
            'requestsHandledByRequestType',
            'requestsHandledByStatus',
            'averageResponseTime',
            'weeklyPerformance'
        ));
    }

}
