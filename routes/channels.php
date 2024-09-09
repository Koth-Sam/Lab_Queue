<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('requests.{id}', function ($user, $id) {
 
    return (int) $user->id === (int) $id;
});
