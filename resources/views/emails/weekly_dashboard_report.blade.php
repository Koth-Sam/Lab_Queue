<!DOCTYPE html>
<html>
<head>
    <title>Weekly Lab Request Report</title>
</head>
<body>
    <h1>Weekly Report for {{ $courseName }} from {{ $weekStartDate }} to {{ $weekEndDate }}</h1>
    
    <h2>Requests Summary</h2>
    <ul>
        <li>Total Requests: {{ $requestsSummary->total_requests }}</li>
        <li>Pending Requests: {{ $requestsSummary->pending_requests }}</li>
        <li>Accepted Requests: {{ $requestsSummary->accepted_requests + $requestsSummary->completed_requests }}</li>
        <li>Completed Requests: {{ $requestsSummary->completed_requests }}</li>
        <li>Sign-Off Requests: {{ $signOffRequests }}</li>
        <li>Assistance Requests: {{ $assistanceRequests }}</li>
    </ul>

    <h2>Feedback Comments (5 indicates Excellent and 1 indicates Poor)</h2>
    @if($feedbackComments->isEmpty())
        <p>No feedback comments for this week.</p>
    @else
        @foreach($feedbackComments->groupBy('request.ta.name') as $taName => $comments)
    <h4>Feedbacks for {{ $taName }}</h4>
    <ul>
        @foreach($comments as $comment)
            <li>{{ $comment->comments }} - Rated {{ $comment->rating }}</li>
        @endforeach
    </ul>
        @endforeach
    @endif

        <h2>Request Status Chart</h2>
        <img src="{{ $chartUrl }}" alt="Requests Chart">

        <h2>Weekly Performance of TAs</h2>
        <img src="{{ $weeklyPerformanceChartUrl }}" alt="Weekly Performance Chart">

        <h2>Requests Handled by TA by Request Type</h2>
        <img src="{{ $requestsByTAChartUrl }}" alt="Requests by TA Chart">

</body>
</html>
