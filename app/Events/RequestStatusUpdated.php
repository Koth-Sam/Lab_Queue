<?php

namespace App\Events;


use App\Models\Request as UserRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;
    public $taName;

    /**
     * Create a new event instance.
     */
    public function __construct(UserRequest $request)
    {
        //
        $this->request = $request;
        $this->taName = $request->ta->name;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('requests.'.$this->request->student_id), new Channel('ta-requests')];
    }

    public function broadcastWith()
    {
        return [
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'ta_name' => $this->taName,
            
        ];
    }
}
