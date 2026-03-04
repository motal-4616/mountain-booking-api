<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Channel xác thực cho cuộc hội thoại
 * Chỉ cho phép user là participant trong conversation
 */
Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    return $conversation && $conversation->hasParticipant($user->id);
});

/**
 * Channel cá nhân của user
 * Dùng để nhận thông báo tin nhắn mới, cập nhật conversation list
 */
Broadcast::channel('user.{userId}', function ($user, int $userId) {
    return $user->id === $userId;
});
