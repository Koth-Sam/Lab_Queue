<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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

    public function destroy(string $id)
    {
        //
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
            ->groupBy('ta_id')
            ->with('ta')
            ->get()
            ->map(function ($request) {
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
            ->groupBy('week', 'ta_id')
            ->orderBy('week', 'asc')
            ->with('ta')
            ->get()
            ->map(function ($item) {
                return [
                    'week' => $item->week,
                    'ta' => $item->ta ? $item->ta->name : 'N/A',
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

    // Fetch the data grouped by TA and request type
    $requestsHandledByTA = UserRequest::where('course_name', $courseName)
        ->select('ta_id', 'request_type', DB::raw('count(*) as count'))
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


 public function exportToPDF()
 {
     // Get the same data used in your admin dashboard view
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
         ->groupBy('subject_area')
         ->orderBy('subject_area', 'asc')
         ->get();
 
     // Render the admin dashboard view to HTML
     $html = view('admin.dashboard', compact(
         'requestsSummary',
         'requestsHandledByTA',
         'weeklyPerformanceByCourse',
         'courses',
         'averageResponseTimeByTA',
         'requestsByCourse',
         'requestsTrend',
         'requestsTrendByType',
         'requestsByTAAndType',
         'requestsByCourseByTAByType',
         'requestsBySubjectArea'
     ))->render();
 
     // Load the HTML to DomPDF
     $pdf = PDF::loadHTML($html);
 
     // Download the PDF
     return $pdf->download('admin_dashboard.pdf');
 }
 
public function exportToWord(Request $request)
{
    // Instantiate PhpWord
    $phpWord = new PhpWord();

    // Add a new section to the Word document
    $section = $phpWord->addSection();

    // Add Title
    $section->addTitle('Admin Dashboard', 1);

    // Decode the JSON data from the request
    $exportData = json_decode($request->getContent(), true);

        // Add Summary
        $requestsSummary = UserRequest::selectRaw('
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests,
            SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_requests,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests
        ')->first();

        // Add summary text to the document
    $section->addText('Total Requests: ' . $requestsSummary->total_requests);
    $section->addText('Pending Requests: ' . $requestsSummary->pending_requests);
    $section->addText('Accepted Requests: ' . $requestsSummary->accepted_requests);
    $section->addText('Completed Requests: ' . $requestsSummary->completed_requests);

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

        // Note: Generate and save charts as images on the server before this step
        $charts = [
            'requestsHandledByTAChart.png',
            'weeklyPerformanceChart.png',
            'TAPerformanceByTypeCourseChart.png',
            'requestsByTAAndTypeChart.png',
            'averageResponseTimeByTAChart.png',
            'requestsByCourseChart.png',
            'requestsTrendChart.png',
            'requestsTrendByTypeChart.png',
        ];

    // Add chart images to the document
    foreach ($exportData['charts'] as $chartId => $dataUrl) {
        if ($dataUrl) { // Check if dataUrl is not null
            // Decode the base64 image
            $imageData = explode(',', $dataUrl)[1] ?? null; // Check if base64 part exists
            if ($imageData) {
                $imagePath = storage_path('app/public/' . $chartId . '.png');
                file_put_contents($imagePath, base64_decode($imageData));

                // Add the image to the Word document
                $section->addImage(
                    $imagePath,
                    [
                        'width' => 500,
                        'height' => 300,
                        'align' => 'center'
                    ]
                );

                // Optionally delete the temporary image file
                unlink($imagePath);
            }
        }
    }

    // Save the document to a temporary file
    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $fileName = 'Admin_Dashboard.docx';
    $filePath = storage_path('app/public/' . $fileName);
    $objWriter->save($filePath);

    // Return the file as a download response
    return response()->download($filePath)->deleteFileAfterSend(true);

}

}
