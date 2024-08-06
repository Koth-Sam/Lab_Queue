<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDashboardReport;
use App\Models\Request as UserRequest;
use App\Models\Feedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use QuickChart;
use Carbon\Carbon;

class SendWeeklyDashboardReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-weekly-dashboard-report';

    //protected $signature = 'report:weekly';
    protected $description = 'Send weekly dashboard reports to lecturers';

    /**
     * The console command description.
     *
     * @var string
     */
    //protected $description = 'Command description';

    /**
     * Execute the console command.
     */

     public function __construct()
     {
         parent::__construct();
     } 

    public function handle()
    {
        //
        $lecturerCourses = config('lecturer_courses.lecturer_courses');

        foreach ($lecturerCourses as $email => $courses) {
            foreach ($courses as $courseName) {
                $requestsSummary = UserRequest::where('course_name', $courseName)
                    ->selectRaw('
                        COUNT(*) as total_requests,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests,
                        SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_requests,
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests
                    ')
                    ->first();

                $feedbackComments = Feedback::whereHas('request', function ($query) use ($courseName) {
                    $query->where('course_name', $courseName);
                })
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->get();

                $chart = new QuickChart([
                    'width' => 500,
                    'height' => 300,
                    'format' => 'png',
                ]);

                $chart->setConfig('{
                    type: "bar",
                    data: {
                        labels: ["Pending", "Accepted", "Completed"],
                        datasets: [{
                            label: "Requests",
                            data: [' . $requestsSummary->pending_requests . ', ' . ($requestsSummary->accepted_requests + $requestsSummary->completed_requests) . ', ' . $requestsSummary->completed_requests . ']
                        }]
                    }
                }');

                $chartUrl = $chart->getUrl();

                Mail::to($email)->send(new WeeklyDashboardReport($requestsSummary, $feedbackComments, $chartUrl, $courseName));

            }
        }
    }

    

}
