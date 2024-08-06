<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDashboardReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

     public $requestsSummary;
     public $feedbackComments;
     public $chartUrl;
     public $courseName;

    public function __construct($requestsSummary, $feedbackComments, $chartUrl, $courseName)
    {
        //
        $this->requestsSummary = $requestsSummary;
        $this->feedbackComments = $feedbackComments;
        $this->chartUrl = $chartUrl;
        $this->courseName = $courseName;

    }

    public function build()
    {
        return $this->view('emails.weekly_dashboard_report')
                    ->subject("Weekly Report for {$this->courseName}")
                    ->with([
                        'requestsSummary' => $this->requestsSummary,
                        'feedbackComments' => $this->feedbackComments,
                        'chartUrl' => $this->chartUrl,
                        'courseName' => $this->courseName,
                    ]);
    }

}
