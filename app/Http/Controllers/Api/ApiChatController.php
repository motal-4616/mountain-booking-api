<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApiChatController extends ApiController
{
    /**
     * Danh sách cuộc hội thoại
     * GET /api/chat/conversations
     */
    public function conversations(Request $request)
    {
        try {
            $user = $request->user();

            $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['lastMessage.user:id,name', 'users:id,name,avatar'])
            ->withCount(['messages as unread_count' => function ($q) use ($user) {
                $q->where('user_id', '!=', $user->id)
                  ->where('created_at', '>', function ($sub) use ($user) {
                      $sub->select('last_read_at')
                          ->from('conversation_participants')
                          ->whereColumn('conversation_id', 'conversations.id')
                          ->where('user_id', $user->id)
                          ->limit(1);
                  });
            }])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get()
            ->map(function ($conversation) use ($user) {
                return $this->formatConversation($conversation, $user);
            });

            return $this->successResponse($conversations, 'Lấy danh sách hội thoại thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách hội thoại: ' . $e->getMessage());
        }
    }

    /**
     * Lấy hoặc tạo cuộc hội thoại riêng với một user
     * POST /api/chat/conversations/private
     */
    public function getOrCreatePrivateConversation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ], [
                'user_id.required' => 'ID người dùng là bắt buộc',
                'user_id.exists' => 'Người dùng không tồn tại',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = $request->user();
            $otherUserId = $request->user_id;

            if ($user->id === $otherUserId) {
                return $this->errorResponse('Không thể tạo hội thoại với chính mình');
            }

            // Kiểm tra có phải bạn bè không
            if (!Friendship::areFriends($user->id, $otherUserId)) {
                return $this->errorResponse('Bạn cần kết bạn trước khi nhắn tin');
            }

            $conversation = Conversation::findOrCreatePrivate($user->id, $otherUserId);
            $conversation->load(['lastMessage.user:id,name', 'users:id,name,avatar']);

            return $this->successResponse(
                $this->formatConversation($conversation, $user),
                'Lấy hội thoại thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi tạo hội thoại: ' . $e->getMessage());
        }
    }

    /**
     * Lấy tin nhắn trong cuộc hội thoại
     * GET /api/chat/conversations/{conversationId}/messages
     */
    public function messages(Request $request, int $conversationId)
    {
        try {
            $user = $request->user();

            $conversation = Conversation::find($conversationId);
            if (!$conversation || !$conversation->hasParticipant($user->id)) {
                return $this->notFoundResponse('Không tìm thấy cuộc hội thoại');
            }

            $query = Message::with('user:id,name,avatar')
                ->where('conversation_id', $conversationId)
                ->latest();

            // Cursor-based pagination: lấy tin nhắn trước một thời điểm
            if ($request->has('before') && $request->before) {
                $query->where('created_at', '<', $request->before);
            }

            $limit = $request->input('limit', 30);
            $messages = $query->limit($limit)->get()->reverse()->values();

            $data = $messages->map(function ($message) use ($user) {
                return $this->formatMessage($message, $user);
            });

            // Cập nhật last_read_at
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);

            return $this->successResponse([
                'messages' => $data,
                'has_more' => $messages->count() === $limit,
            ], 'Lấy tin nhắn thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy tin nhắn: ' . $e->getMessage());
        }
    }

    /**
     * Gửi tin nhắn
     * POST /api/chat/conversations/{conversationId}/messages
     */
    public function sendMessage(Request $request, int $conversationId)
    {
        try {
            $user = $request->user();

            $conversation = Conversation::find($conversationId);
            if (!$conversation || !$conversation->hasParticipant($user->id)) {
                return $this->notFoundResponse('Không tìm thấy cuộc hội thoại');
            }

            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:text,image,video,voice,location',
                'body' => 'required_if:type,text|required_without:type|nullable|string|max:5000',
                'image' => 'required_if:type,image|nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
                'video' => 'required_if:type,video|nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,video/3gpp|max:51200',
                'voice' => 'required_if:type,voice|nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
                'latitude' => 'required_if:type,location|nullable|numeric',
                'longitude' => 'required_if:type,location|nullable|numeric',
                'location_name' => 'nullable|string|max:200',
            ], [
                'body.required_if' => 'Nội dung tin nhắn là bắt buộc',
                'body.required_without' => 'Nội dung tin nhắn là bắt buộc',
                'body.max' => 'Tin nhắn không được vượt quá 5000 ký tự',
                'image.required_if' => 'Ảnh là bắt buộc cho tin nhắn dạng hình ảnh',
                'image.max' => 'Ảnh không được vượt quá 10MB',
                'image.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg hoặc webp',
                'video.required_if' => 'Video là bắt buộc cho tin nhắn dạng video',
                'video.max' => 'Video không được vượt quá 50MB',
                'video.mimes' => 'Video phải có định dạng mp4, mov, avi hoặc webm',
                'voice.max' => 'File ghi âm không được vượt quá 10MB',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $type = $request->input('type', 'text');
            $body = $request->body;
            $metadata = null;

            // Xử lý theo loại tin nhắn
            switch ($type) {
                case 'image':
                    $path = $request->file('image')->store('chat/images', 'public');
                    $body = $path;
                    $metadata = ['original_name' => $request->file('image')->getClientOriginalName()];
                    break;

                case 'video':
                    if (!$request->hasFile('video')) {
                        return $this->errorResponse('Video file không hợp lệ');
                    }
                    $videoFile = $request->file('video');
                    if (!$videoFile->isValid()) {
                        return $this->errorResponse('Video file bị lỗi khi upload');
                    }
                    $path = $videoFile->store('chat/videos', 'public');
                    $body = $path;
                    $metadata = [
                        'original_name' => $videoFile->getClientOriginalName(),
                        'mime_type' => $videoFile->getMimeType(),
                        'size' => $videoFile->getSize(),
                    ];
                    break;

                case 'voice':
                    $path = $request->file('voice')->store('chat/voices', 'public');
                    $body = $path;
                    $metadata = ['duration' => $request->input('duration', 0)];
                    break;

                case 'location':
                    $body = $request->location_name ?? 'Vị trí đã chia sẻ';
                    $metadata = [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'location_name' => $request->location_name,
                    ];
                    break;
            }

            $message = Message::create([
                'conversation_id' => $conversationId,
                'user_id' => $user->id,
                'type' => $type,
                'body' => $body,
                'metadata' => $metadata,
            ]);

            // Cập nhật last_message_id
            $conversation->update(['last_message_id' => $message->id]);

            // Cập nhật last_read_at cho sender
            ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);

            $message->load('user:id,name,avatar');

            return $this->successResponse(
                $this->formatMessage($message, $user),
                'Gửi tin nhắn thành công',
                201
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi gửi tin nhắn: ' . $e->getMessage());
        }
    }

    /**
     * Đánh dấu đã đọc
     * POST /api/chat/conversations/{conversationId}/read
     */
    public function markAsRead(Request $request, int $conversationId)
    {
        try {
            $user = $request->user();

            $participant = ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$participant) {
                return $this->notFoundResponse('Không tìm thấy cuộc hội thoại');
            }

            $participant->update(['last_read_at' => now()]);

            return $this->successResponse(null, 'Đã đánh dấu đã đọc');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi đánh dấu đã đọc: ' . $e->getMessage());
        }
    }

    /**
     * Bật/tắt thông báo hội thoại
     * POST /api/chat/conversations/{conversationId}/mute
     */
    public function toggleMute(Request $request, int $conversationId)
    {
        try {
            $user = $request->user();

            $participant = ConversationParticipant::where('conversation_id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$participant) {
                return $this->notFoundResponse('Không tìm thấy cuộc hội thoại');
            }

            $participant->update(['is_muted' => !$participant->is_muted]);

            return $this->successResponse([
                'is_muted' => !$participant->is_muted,
            ], $participant->is_muted ? 'Đã tắt thông báo' : 'Đã bật thông báo');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật thông báo: ' . $e->getMessage());
        }
    }

    /**
     * Xóa tin nhắn (chỉ xóa cho người gửi)
     * DELETE /api/chat/messages/{messageId}
     */
    public function deleteMessage(Request $request, int $messageId)
    {
        try {
            $message = Message::find($messageId);

            if (!$message) {
                return $this->notFoundResponse('Không tìm thấy tin nhắn');
            }

            if ($message->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền xóa tin nhắn này');
            }

            // Xóa file nếu là ảnh/voice
            if (in_array($message->type, ['image', 'voice']) && $message->body) {
                Storage::disk('public')->delete($message->body);
            }

            $conversationId = $message->conversation_id;
            $message->delete();

            // Cập nhật last_message_id nếu cần
            $conversation = Conversation::find($conversationId);
            if ($conversation && $conversation->last_message_id === $messageId) {
                $latestMessage = Message::where('conversation_id', $conversationId)->latest()->first();
                $conversation->update(['last_message_id' => $latestMessage?->id]);
            }

            return $this->successResponse(null, 'Đã xóa tin nhắn');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa tin nhắn: ' . $e->getMessage());
        }
    }

    /**
     * Đếm tổng tin nhắn chưa đọc
     * GET /api/chat/unread-count
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = $request->user();

            $totalUnread = 0;
            $participations = ConversationParticipant::where('user_id', $user->id)->get();

            foreach ($participations as $participant) {
                $count = Message::where('conversation_id', $participant->conversation_id)
                    ->where('user_id', '!=', $user->id)
                    ->when($participant->last_read_at, function ($q) use ($participant) {
                        $q->where('created_at', '>', $participant->last_read_at);
                    })
                    ->count();
                $totalUnread += $count;
            }

            return $this->successResponse([
                'unread_count' => $totalUnread,
            ], 'Lấy số tin nhắn chưa đọc thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    // ===== Helpers =====

    private function formatConversation(Conversation $conversation, $currentUser): array
    {
        $otherUsers = $conversation->users->filter(fn($u) => $u->id !== $currentUser->id);
        $otherUser = $otherUsers->first();

        $participant = $conversation->participants
            ? $conversation->participants->firstWhere('user_id', $currentUser->id)
            : null;

        return [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->type === 'private' && $otherUser
                ? $otherUser->name
                : ($conversation->name ?? 'Nhóm'),
            'avatar_url' => $conversation->type === 'private' && $otherUser
                ? $otherUser->avatar_url
                : ($conversation->avatar ? asset('storage/' . $conversation->avatar) : null),
            'other_user' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar_url' => $otherUser->avatar_url,
            ] : null,
            'last_message' => $conversation->lastMessage ? [
                'id' => $conversation->lastMessage->id,
                'body' => $conversation->lastMessage->preview,
                'type' => $conversation->lastMessage->type,
                'user_name' => $conversation->lastMessage->user?->name,
                'is_mine' => $conversation->lastMessage->user_id === $currentUser->id,
                'created_at' => $conversation->lastMessage->created_at->toISOString(),
            ] : null,
            'unread_count' => $conversation->unread_count ?? 0,
            'is_muted' => $participant?->is_muted ?? false,
            'updated_at' => $conversation->lastMessage?->created_at?->toISOString()
                ?? $conversation->created_at->toISOString(),
        ];
    }

    private function formatMessage(Message $message, $currentUser): array
    {
        $data = [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'type' => $message->type,
            'body' => $message->body,
            'metadata' => $message->metadata,
            'is_mine' => $message->user_id === $currentUser->id,
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'avatar_url' => $message->user->avatar_url,
            ],
            'created_at' => $message->created_at->toISOString(),
        ];

        // Thêm URL cho ảnh/video/voice
        if ($message->type === 'image' && $message->body) {
            $data['image_url'] = $message->image_url;
        }

        if ($message->type === 'video' && $message->body) {
            $data['video_url'] = $message->video_url;
        }

        return $data;
    }
}
