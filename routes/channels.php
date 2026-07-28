<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = \Illuminate\Support\Facades\DB::table('conversations')->where('id', $conversationId)->first();
    if (!$conversation) {
        return false;
    }
    return (int) $user->id === (int) $conversation->user_one_id || (int) $user->id === (int) $conversation->user_two_id;
});
