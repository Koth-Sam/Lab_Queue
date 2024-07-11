<?php

namespace App\Http\Controllers;

use App\Models\Request as UserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TAController extends Controller
{
    public function index()
    {
        $requests = UserRequest::orderBy('requested_at', 'desc')->get();
        return view('ta.index', compact('requests'));
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

        return view('ta.dashboard', compact('requestsHandled', 'requestsByStatus'));
    }
}
