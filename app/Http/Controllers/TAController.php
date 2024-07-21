<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TAController extends Controller
{
    public function index(Request $request)
    {
        
        $query = UserRequest::query();

        // Handle filtering
        if ($request->has('course_name')) {
            $courseNames = explode(',', $request->course_name);
            $query->whereIn('course_name', $courseNames);
        }

        if ($request->has('course_code')) {
            $courseCodes = explode(',', $request->course_code);
            $query->whereIn('course_code', $courseCodes);
        }

        if ($request->has('request_type')) {
            $requestTypes = explode(',', $request->request_type);
            $query->whereIn('request_type', $requestTypes);
        }

        if ($request->has('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->has('ta_name')) {
            $taNames = explode(',', $request->ta_name);
            if (in_array('N/A', $taNames)) {
                // Include rows where ta_id is null or matches any selected TA names
                $query->where(function ($q) use ($taNames) {
                    $q->whereNull('ta_id')->orWhereIn('ta_id', function ($query) use ($taNames) {
                        $query->select('id')
                            ->from('users')
                            ->whereIn('name', $taNames);
                    });
                });
            } else {
                // Regular filter by ta_id
                $query->whereHas('ta', function ($q) use ($taNames) {
                    $q->whereIn('name', $taNames);
                });
            }
        }

        // Handle sorting
        $sortField = $request->get('sort', 'requested_at');
        $sortOrder = $request->get('order', 'desc');
        $requests = $query->orderBy($sortField, $sortOrder)->get();

        // Get unique values for filters
        $uniqueCourses = UserRequest::distinct()->pluck('course_name');
        $uniqueCourseCodes = UserRequest::distinct()->pluck('course_code');
        $uniqueRequestTypes = UserRequest::distinct()->pluck('request_type');
        $uniqueStatuses = UserRequest::distinct()->pluck('status');
        $uniqueTANames = UserRequest::with('ta')->get()->pluck('ta.name')->filter()->unique()->values();
        if (! $uniqueTANames->contains('N/A')) {
            $uniqueTANames->push('N/A');
        }

        return view('ta.index', compact('requests', 'uniqueCourses', 'uniqueCourseCodes', 'uniqueRequestTypes', 'uniqueStatuses', 'uniqueTANames'));
    }

    public function show($id)
    {
        $request = UserRequest::findOrFail($id);
        $request->screenshot = json_decode($request->screenshot);
        return view('ta.show', compact('request'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,accepted,completed',
        ]);

        $userRequest = UserRequest::findOrFail($id);
        $userRequest->status = $validatedData['status'];

        if ($validatedData['status'] == 'accepted') {
            $userRequest->ta_id = Auth::id();
            $userRequest->accepted_at = now();
        } elseif ($validatedData['status'] == 'completed') {
           
            if (!$userRequest->ta_id) {
                $userRequest->ta_id = Auth::id();
                $userRequest->accepted_at = now();
            }
            $userRequest->completed_at = now();
        }
        
        $userRequest->save();

        return redirect()->route('ta.index')->with('success', 'Request status updated successfully.');
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
    
        $requestsHandledByCourse = UserRequest::where('ta_id', $taId)
            ->selectRaw('course_name, count(*) as count')
            ->groupBy('course_name')
            ->orderBy('course_name', 'asc')
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
            'requestsHandledByCourse',
            'weeklyPerformance'
        ));
    }
}
