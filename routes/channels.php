<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });


Broadcast::channel('requests.{id}', function ($user, $id) {
    // Authorize the user to listen to the channel if the user's ID matches the student ID
    return (int) $user->id === (int) $id;
});
