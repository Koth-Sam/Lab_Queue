<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class AdminController extends Controller
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
        
        return view('admin.index', compact('requests', 'uniqueCourses', 'uniqueCourseCodes', 'uniqueRequestTypes', 'uniqueStatuses', 'uniqueTANames'));

    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        $request = UserRequest::findOrFail($id);
        $request->screenshot = json_decode($request->screenshot);

        return view('admin.show', compact('request'));
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $userRequest = UserRequest::findOrFail($id);
        $userRequest->status = $request->status;

        if ($request->status == 'In-Progress') {
            $userRequest->ta_id = Auth::id();
            $userRequest->accepted_at = now();
        } elseif ($request->status == 'Completed') {
            $userRequest->completed_at = now();
        }

        $userRequest->save();

        return redirect()->route('admin.index');
    }

    
    public function dashboard()
    {
        $requestsSummary = UserRequest::selectRaw('
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests,
            SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_requests,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests
        ')->first();

        $requestsHandledByTA = UserRequest::selectRaw('
            ta_id,
            COUNT(*) as count
        ')->groupBy('ta_id')->with('ta')->get();

        $weeklyPerformanceByCourse = UserRequest::selectRaw('
            YEARWEEK(requested_at, 3) as week,
            course_name,
            COUNT(*) as count
        ')
        ->groupBy('week', 'course_name')
        ->orderBy('week', 'asc')
        ->get();

        $courses = UserRequest::distinct()->pluck('course_name');

        $averageResponseTimeByTA = UserRequest::whereNotNull('completed_at')
            ->selectRaw('
                ta_id,
                AVG(TIMESTAMPDIFF(MINUTE, accepted_at, completed_at)) as avg_response_time
            ')->groupBy('ta_id')->with('ta')->get();

        $requestsByCourse = UserRequest::selectRaw('
            course_name,
            COUNT(*) as count
        ')->groupBy('course_name')->get();

        $requestsTrend = UserRequest::selectRaw('
            DATE(requested_at) as date,
            COUNT(*) as count
        ')->groupBy('date')->orderBy('date', 'asc')->get();

        $requestsTrendByType = UserRequest::selectRaw('DATE(requested_at) as date, request_type, count(*) as count')
            ->groupBy('date', 'request_type')
            ->orderBy('date', 'asc')
            ->get();

        $requestsByTAAndType = UserRequest::select('ta_id', 'request_type', DB::raw('count(*) as count'))
            ->whereIn('status', ['accepted', 'completed'])
            ->groupBy('ta_id', 'request_type')
            ->with('ta:id,name')
            ->get()
            ->groupBy('ta_id')
            ->map(function ($requests, $taId) {
                $taName = $requests->first()->ta->name ?? 'N/A';
                $assistance = $requests->where('request_type', 'assistance')->sum('count');
                $signOff = $requests->where('request_type', 'sign-off')->sum('count');

                return [
                    'ta' => $taName,
                    'assistance' => $assistance,
                    'sign-off' => $signOff,
                ];
            })
            ->values();

        $requestsByCourseByTAByType = UserRequest::selectRaw('
            course_name,
            ta_id,
            request_type,
            COUNT(*) as count
        ')
            ->groupBy('course_name', 'ta_id', 'request_type')
            ->with('ta:id,name')
            ->get()
            ->groupBy('course_name')
            ->map(function ($requestsByTA, $courseName) {
            return $requestsByTA->groupBy('ta_id')
                ->map(function ($requests, $taId) use ($courseName) {
                    $taName = $requests->first()->ta->name ?? 'N/A';
                    $assistance = $requests->where('request_type', 'assistance')->sum('count');
                    $signOff = $requests->where('request_type', 'sign-off')->sum('count');
    
                    return [
                        'course' => $courseName,
                        'ta' => $taName,
                        'assistance' => $assistance,
                        'sign-off' => $signOff,
                    ];
                });
        });

        $requestsBySubjectArea = UserRequest::select('subject_area', DB::raw('count(*) as count'))
        ->where('course_name')
        ->groupBy('subject_area')
        ->orderBy('subject_area', 'asc')
        ->get();

        $ratingsByTA = $this->getRatingsByTA();

        return view('admin.dashboard', compact(
            'requestsSummary',
            'requestsHandledByTA',
            'weeklyPerformanceByCourse',
            //'courses',
            'averageResponseTimeByTA',
            'requestsByCourse',
            'requestsTrend',
            'requestsTrendByType',
            'requestsByTAAndType',
            'requestsByCourseByTAByType',
            'requestsBySubjectArea',
            'ratingsByTA'
        ));
    }

    public function getCourses()
    {
        $courses = UserRequest::distinct()->pluck('course_name');
        return response()->json($courses);
    }

    public function getRequestsHandledByTA(Request $request)
    {
        $courseName = $request->query('course_name');

        $requestsHandledByTA = UserRequest::where('course_name', $courseName)
            ->select('ta_id', DB::raw('count(*) as count'))
            ->whereNotNull('ta_id') 
            ->groupBy('ta_id')
            ->with('ta')
            ->get()
            ->map(function ($request) {
                $taName = $request->first()->ta->name;  
                return [
                    'ta' => $request->ta ? $request->ta->name : 'N/A',
                    'count' => $request->count,
                ];
            });

        return response()->json($requestsHandledByTA);
    }

    public function getWeeklyPerformance(Request $request)
    {
        $courseName = $request->query('course_name');
    
        $weeklyPerformanceByCourse = UserRequest::selectRaw('YEARWEEK(requested_at, 3) as week, ta_id, count(*) as count')
            ->where('course_name', $courseName)
            ->whereIn('status', ['accepted', 'completed'])  // Include only accepted or completed requests
            ->whereNotNull('ta_id')  // Exclude requests with no assigned TA
            ->groupBy('week', 'ta_id')
            ->orderBy('week', 'asc')
            ->with('ta:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'week' => $item->week,
                    'ta' => $item->ta->name,  // No need for 'N/A' fallback as we're excluding null TAs
                    'count' => $item->count
                ];
            });
    
        return response()->json($weeklyPerformanceByCourse);
    }
    


    public function getRequestsByTAAndType()
    {
        // Fetch and group data
        $data = UserRequest::select('ta_id', 'request_type', DB::raw('count(*) as count'))
            ->whereIn('status', ['accepted', 'completed']) // Ensure to only consider accepted and completed requests
            ->groupBy('ta_id', 'request_type')
            ->with('ta:id,name') // Ensure `ta` relationship is eager loaded with only required fields
            ->get()
            ->groupBy('ta_id'); // Group data by TA

        // Format data for chart
        $formattedData = $data->map(function ($requests, $taId) {
            $ta = $requests->first()->ta; // Assuming all requests for a TA have the same TA data
            return [
                'ta' => $ta ? $ta->name : 'Unknown',
                'assistance' => $requests->where('request_type', 'assistance')->sum('count'),
                'sign-off' => $requests->where('request_type', 'sign-off')->sum('count'),
            ];
        })->values(); // Convert to a collection

        return response()->json($formattedData);
    }

    public function getRequestsHandledByTAByCourse(Request $request)
 {
    $courseName = $request->query('course_name');

    if (!$courseName) {
        return response()->json([]);
    }

        $requestsHandledByTA = UserRequest::where('course_name', $courseName)
        ->whereIn('status', ['accepted', 'completed'])  // Include only accepted or completed requests
        ->whereNotNull('ta_id')  // Exclude requests with no assigned TA
        ->select('ta_id', 'request_type', DB::raw('count(*) as count'))
        ->groupBy('ta_id', 'request_type')
        ->with('ta:id,name')
        ->get()
        ->groupBy('ta_id')
        ->map(function ($requests, $taId) {
            $taName = $requests->first()->ta->name;  // No need for 'N/A' fallback as we're excluding null TAs
            $assistance = $requests->where('request_type', 'assistance')->sum('count');
            $signOff = $requests->where('request_type', 'sign-off')->sum('count');

            return [
                'ta' => $taName,
                'assistance' => $assistance,
                'sign-off' => $signOff,
            ];
        })
        ->values();

    return response()->json($requestsHandledByTA);
 }

    public function getRequestsBySubjectArea(Request $request)
    {
     $courseName = $request->query('course_name');
 
     // Fetch requests grouped by subject_area for the given course
     $requestsBySubjectArea = UserRequest::select('subject_area', DB::raw('count(*) as count'))
         ->when($courseName, function ($query, $courseName) {
             return $query->where('course_name', $courseName);
         })
         ->groupBy('subject_area')
         ->orderBy('subject_area', 'asc')
         ->get()
         ->map(function ($request) {
             return [
                 'subject_area' => $request->subject_area ?? 'Unknown', // Ensure 'Unknown' is handled
                 'count' => $request->count,
             ];
         });
 
     return response()->json($requestsBySubjectArea);

    } 

    public function getRatingsByTA()
{
    // Assuming there is a relationship between UserRequest and Feedback
    $ratingsByTA = UserRequest::join('feedback', 'requests.id', '=', 'feedback.request_id') // Adjust the foreign key if needed
        ->whereNotNull('feedback.rating') // Ensure only requests with ratings are considered
        ->where('requests.status', 'completed') // Only completed requests
        ->select('requests.ta_id', 'feedback.rating', DB::raw('count(*) as count'))
        ->groupBy('requests.ta_id', 'feedback.rating')
        ->with('ta:id,name') // Ensure `ta` relationship is eager loaded
        ->get()
        ->groupBy('ta_id')
        ->map(function ($ratings, $taId) {
            $taName = $ratings->first()->ta->name ?? 'Unknown'; // Handle TA name
            $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]; // Initialize rating counts

            foreach ($ratings as $rating) {
                $ratingCounts[$rating->rating] = $rating->count; // Populate counts for each rating
            }

            return [
                'ta' => $taName,
                'ratings' => $ratingCounts,
            ];
        })
        ->values();

    return $ratingsByTA;
}

}
