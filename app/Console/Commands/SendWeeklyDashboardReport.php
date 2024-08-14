<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDashboardReport;
use App\Models\Request as UserRequest;
use App\Models\Feedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use QuickChart;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendWeeklyDashboardReport extends Command
{
    protected $signature = 'app:send-weekly-dashboard-report';
    protected $description = 'Send weekly dashboard reports to lecturers';

    public function __construct()
    {
        parent::__construct();
    } 

    public function handle()
{
    $lecturerCourses = config('lecturer_courses.lecturer_courses');

    foreach ($lecturerCourses as $email => $courses) {
        foreach ($courses as $courseName) {

            $weekStartDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $weekEndDate = Carbon::now()->endOfWeek()->format('Y-m-d');

            $requestsSummary = UserRequest::where('course_name', $courseName)
                ->selectRaw('
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests,
                    SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_requests,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests
                ')
                ->first();

            $signOffRequests = UserRequest::where('course_name', $courseName)
                ->where('request_type', 'sign-off')
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count();

            $assistanceRequests = UserRequest::where('course_name', $courseName)
                ->where('request_type', 'assistance')
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->count();

            $feedbackComments = Feedback::whereHas('request', function ($query) use ($courseName) {
                $query->where('course_name', $courseName);
            })
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->get();

            // Request Status Chart
            $chart = new QuickChart();
            $chartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => ["Pending", "Accepted", "Completed"],
                    'datasets' => [
                        [
                            'label' => 'Requests',
                            'data' => [
                                (int) $requestsSummary->pending_requests,
                                (int) ($requestsSummary->accepted_requests + $requestsSummary->completed_requests),
                                (int) $requestsSummary->completed_requests,
                            ],
                        ],
                    ],
                ],
                'options' => [
                    'scales' => [
                        'x' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Request Status',
                            ],
                        ],
                        'y' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Number of Requests',
                            ],
                            'ticks' => [
                                'beginAtZero' => true,
                                'stepSize' => 1,
                                'precision' => 0,
                            ],
                        ],
                    ],
                ],
            ];

            // Log the configuration for debugging purposes
            Log::info('Request Status Chart Config:', ['config' => $chartConfig]);

            // Set Chart.js version and device pixel ratio for better rendering
            $chart->setConfig(json_encode($chartConfig));
            $chart->setVersion('4');  // Ensure the use of Chart.js v4
            $chart->setDevicePixelRatio(2);  // High-quality rendering
            $chart->setWidth(300);  // Set the chart width to 400px
            $chart->setHeight(200);

            $chartUrl = $chart->getUrl();

            // Weekly Performance of TAs Chart
            $weeklyPerformanceChart = new QuickChart();

            $weeklyPerformanceData = UserRequest::where('course_name', $courseName)
                ->selectRaw('
                    DATE_FORMAT(requested_at, "%Y-%u") as week,
                    COUNT(*) as count
                ')
                ->groupBy('week')
                ->get();

            $weeklyLabels = $weeklyPerformanceData->pluck('week')->toArray();
            $weeklyCounts = $weeklyPerformanceData->pluck('count')->toArray();

            $weeklyPerformanceChartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $weeklyLabels,
                    'datasets' => [
                        [
                            'label' => 'Weekly Requests',
                            'data' => $weeklyCounts,
                        ],
                    ],
                ],
                'options' => [
                    'scales' => [
                        'x' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Week',
                            ],
                        ],
                        'y' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Number of Requests',
                            ],
                            'ticks' => [
                                'beginAtZero' => true,
                                'stepSize' => 1,
                                'precision' => 0,
                            ],
                        ],
                    ],
                ],
            ];

            $weeklyPerformanceChart->setConfig(json_encode($weeklyPerformanceChartConfig));
            $weeklyPerformanceChart->setVersion('4');  // Ensure the use of Chart.js v4
            $weeklyPerformanceChart->setDevicePixelRatio(2);  // High-quality rendering
            $weeklyPerformanceChart->setWidth(300);  // Set the chart width to 400px
            $weeklyPerformanceChart->setHeight(200);

            $weeklyPerformanceChartUrl = $weeklyPerformanceChart->getUrl();

         // Requests Handled by TA by Request Type by Course Chart
$requestsByTAChart = new QuickChart();

// Join the user table to get TA names
$requestsByTAData = UserRequest::where('course_name', $courseName)
    ->whereIn('status', ['accepted', 'completed'])
    ->join('users', 'users.id', '=', 'requests.ta_id')
    ->selectRaw('users.name as ta_name, request_type, COUNT(*) as count')
    ->groupBy('ta_name', 'request_type')
    ->get();

    $tas = $requestsByTAData->pluck('ta_name')->unique()->toArray();
$requestTypes = ['assistance', 'sign-off'];

$datasets = [];
foreach ($requestTypes as $type) {
    $data = [];
    foreach ($tas as $ta) {
        $count = $requestsByTAData->where('ta_name', $ta)->where('request_type', $type)->sum('count');
        $data[] = $count;
    }
    $datasets[] = [
        'label' => ucfirst($type),
        'data' => $data,
        'backgroundColor' => $type === 'assistance' ? 'rgba(75, 192, 192, 0.6)' : 'rgba(255, 99, 132, 0.6)',
    ];
}

$requestsByTAChartConfig = [
    'type' => 'bar',
    'data' => [
        'labels' => $tas,  // Use TA names for x-axis labels
        'datasets' => $datasets,
        
    ],
    'options' => [
        'scales' => [
            'x' => [
                'title' => [
                    'display' => true,
                    'text' => 'Teaching Assistants',
                ],
            ],
            'y' => [
                'title' => [
                    'display' => true,
                    'text' => 'Number of Requests',
                ],
                'ticks' => [
                    'beginAtZero' => true,
                    'stepSize' => 1,
                    'precision' => 0,
                ],
            ],
        ],
    ],
];

$requestsByTAChart->setConfig(json_encode($requestsByTAChartConfig));
$requestsByTAChart->setVersion('4');  // Ensure the use of Chart.js v4
$requestsByTAChart->setDevicePixelRatio(2);  // High-quality rendering
$requestsByTAChart->setWidth(300);  // Set the chart width to 400px
$requestsByTAChart->setHeight(200);

$requestsByTAChartUrl = $requestsByTAChart->getUrl();

// Send the email with all charts
Mail::to($email)->send(new WeeklyDashboardReport(
    $requestsSummary, 
    $feedbackComments, 
    $chartUrl, 
    $weeklyPerformanceChartUrl,
    $requestsByTAChartUrl,
    $courseName,
    $signOffRequests,
    $assistanceRequests,
    $weekStartDate,
    $weekEndDate
));

        }
    }
}
}
