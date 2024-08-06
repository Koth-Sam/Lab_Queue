
<!DOCTYPE html>
<html>
<head>
    <title>Weekly Lab Request Report</title>
</head>
<body>
    <h1>Weekly Report for {{ $courseName }}</h1>
    
    <h2>Requests Summary</h2>
    <ul>
        <li>Total Requests: {{ $requestsSummary->total_requests }}</li>
        <li>Pending Requests: {{ $requestsSummary->pending_requests }}</li>
        <li>Accepted Requests: {{ $requestsSummary->accepted_requests + $requestsSummary->completed_requests }}</li>
        <li>Completed Requests: {{ $requestsSummary->completed_requests }}</li>
    </ul>

    <h2>Feedback Comments</h2>
    @if($feedbackComments->isEmpty())
        <p>No feedback comments for this week.</p>
    @else
        <ul>
            @foreach($feedbackComments as $comment)
                <li>{{ $comment->content }} - Rated {{ $comment->rating }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Request Status Chart</h2>
    <img src="{{ $chartUrl }}" alt="Requests Chart">

</body>
</html>
