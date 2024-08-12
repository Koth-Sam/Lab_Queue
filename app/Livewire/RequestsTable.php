<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Request as UserRequest;

use Illuminate\Support\Facades\Log;

class RequestsTable extends Component
{
    public $requests;
    public $uniqueCourses;
    public $uniqueCourseCodes;
    public $uniqueRequestTypes;
    public $uniqueStatuses;
    public $uniqueTANames;

    protected $listeners = ['refreshRequests' => 'loadRequests'];

    public function mount($requests, $uniqueCourses, $uniqueCourseCodes, $uniqueRequestTypes, $uniqueStatuses, $uniqueTANames)
    {
       
        $this->requests = $requests;
        $this->uniqueCourses = $uniqueCourses;
        $this->uniqueCourseCodes = $uniqueCourseCodes;
        $this->uniqueRequestTypes = $uniqueRequestTypes;
        $this->uniqueStatuses = $uniqueStatuses;
        $this->uniqueTANames = $uniqueTANames;
    }

    public function loadRequests()
    {
        Log::info('Refreshing Requests...');
        $this->requests = UserRequest::with('ta')->get();
    
    }

    public function render()
    {
        return view('livewire.requests-table', [
            'requests' => $this->requests,
            'uniqueCourses' => $this->uniqueCourses,
            'uniqueCourseCodes' => $this->uniqueCourseCodes,
            'uniqueRequestTypes' => $this->uniqueRequestTypes,
            'uniqueStatuses' => $this->uniqueStatuses,
            'uniqueTANames' => $this->uniqueTANames,
        ]);
    }
}
