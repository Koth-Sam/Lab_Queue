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
                ->where('created_at', '>=', Carbon::now()->subWeek())
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
                
                'data' => [
                    (int) $requestsSummary->pending_requests,
                    (int) $requestsSummary->accepted_requests + (int) $requestsSummary->completed_requests,
                    (int) $requestsSummary->completed_requests,
                ],
                'backgroundColor' => [
                    'rgba(235, 16, 27, 0.6)', 
                    'rgba(236, 175, 9, 0.6)',
                    'rgba(7, 204, 10, 0.6)',
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
                    'plugins' => [
                    'legend' => [
                    'display' => false,
            ],
        ],
                ],
            ];

            $chart->setConfig(json_encode($chartConfig));
            $chart->setVersion('4');
            $chart->setDevicePixelRatio(2);
            $chart->setWidth(300);
            $chart->setHeight(200);

            $chartUrl = $chart->getUrl();

            $subjectAreaData = UserRequest::where('course_name', $courseName)
                ->selectRaw('subject_area, COUNT(*) as count')
                ->groupBy('subject_area')
                ->get();

            $subjectLabels = $subjectAreaData->pluck('subject_area')->toArray();
            $requestCounts = $subjectAreaData->pluck('count')->toArray();

            $subjectAreaChart = new QuickChart();
            $subjectAreaChartConfig = [
                'type' => 'bar',
                'data' => [
                    'labels' => $subjectLabels,
                    'datasets' => [
                        [
                            
                            'data' => $requestCounts,
                            'backgroundColor' => [
                                'rgba(198, 231, 19, 0.6)', 
                                'rgba(34, 233, 136, 0.6)', 
                                'rgba(245, 129, 34, 0.6)', 
                                'rgba(228, 117, 243, 0.6)', 
                                'rgba(153, 102, 255, 0.6)'
                            ],
                            
                        ],
                    ],
                ],
                'options' => [
                    'scales' => [
                        'x' => [
                            'title' => [
                                'display' => true,
                                'text' => 'Subject Area',
                            ],
                            'grid' => [
                                'display' => false,
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
                    'plugins' => [
                    'legend' => [
                    'display' => false,
            ],
                ],
            ],
            ];

            $subjectAreaChart->setConfig(json_encode($subjectAreaChartConfig));
            $subjectAreaChart->setVersion('4');
            $subjectAreaChart->setDevicePixelRatio(2);
            $subjectAreaChart->setWidth(300);
            $subjectAreaChart->setHeight(200);

            $subjectAreaChartUrl = $subjectAreaChart->getUrl();

         // Requests Handled by TA by Request Type by Course Chart
        $requestsByTAChart = new QuickChart();

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
                'backgroundColor' => $type === 'assistance' ? 'rgba(50, 34, 244, 0.6)' : 'rgba(50, 157, 20, 0.6)',
            ];
        }

        $requestsByTAChartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $tas,
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
        $requestsByTAChart->setVersion('4');
        $requestsByTAChart->setDevicePixelRatio(2);
        $requestsByTAChart->setWidth(300);
        $requestsByTAChart->setHeight(200);

        $requestsByTAChartUrl = $requestsByTAChart->getUrl();

    Mail::to($email)->send(new WeeklyDashboardReport(
        $requestsSummary, 
        $feedbackComments, 
        $chartUrl, 
        $subjectAreaChartUrl,
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
