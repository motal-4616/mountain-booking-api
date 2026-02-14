<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserFollow;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiFriendController extends ApiController
{
    /**
     * Danh sách bạn bè
     * GET /api/friends
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $friendIds = Friendship::getFriendIds($user->id);

            $query = User::whereIn('id', $friendIds)
                ->select('id', 'name', 'avatar', 'bio');

            // Tìm kiếm theo tên
            if ($request->has('search') && $request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $friends = $query->orderBy('name')->get()->map(function ($friend) use ($user) {
                return [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'avatar_url' => $friend->avatar_url,
                    'bio' => $friend->bio,
                    'friendship_status' => 'accepted',
                ];
            });

            return $this->successResponse($friends, 'Lấy danh sách bạn bè thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách bạn bè: ' . $e->getMessage());
        }
    }

    /**
     * Lời mời kết bạn đang chờ (nhận được)
     * GET /api/friends/requests
     */
    public function pendingRequests(Request $request)
    {
        try {
            $user = $request->user();

            $requests = Friendship::with('sender:id,name,avatar,bio')
                ->pendingFor($user->id)
                ->latest()
                ->get()
                ->map(function ($friendship) {
                    return [
                        'id' => $friendship->id,
                        'user' => [
                            'id' => $friendship->sender->id,
                            'name' => $friendship->sender->name,
                            'avatar_url' => $friendship->sender->avatar_url,
                            'bio' => $friendship->sender->bio,
                        ],
                        'created_at' => $friendship->created_at->toISOString(),
                    ];
                });

            return $this->successResponse($requests, 'Lấy danh sách lời mời kết bạn thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy lời mời kết bạn: ' . $e->getMessage());
        }
    }

    /**
     * Lời mời kết bạn đã gửi
     * GET /api/friends/sent
     */
    public function sentRequests(Request $request)
    {
        try {
            $user = $request->user();

            $requests = Friendship::with('receiver:id,name,avatar,bio')
                ->sentBy($user->id)
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(function ($friendship) {
                    return [
                        'id' => $friendship->id,
                        'user' => [
                            'id' => $friendship->receiver->id,
                            'name' => $friendship->receiver->name,
                            'avatar_url' => $friendship->receiver->avatar_url,
                            'bio' => $friendship->receiver->bio,
                        ],
                        'created_at' => $friendship->created_at->toISOString(),
                    ];
                });

            return $this->successResponse($requests, 'Lấy danh sách lời mời đã gửi thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy lời mời đã gửi: ' . $e->getMessage());
        }
    }

    /**
     * Gửi lời mời kết bạn
     * POST /api/friends/request
     */
    public function sendRequest(Request $request)
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
            $receiverId = $request->user_id;

            // Không thể kết bạn với chính mình
            if ($user->id === $receiverId) {
                return $this->errorResponse('Bạn không thể kết bạn với chính mình');
            }

            // Kiểm tra đã có quan hệ chưa
            $existingStatus = Friendship::getStatus($user->id, $receiverId);
            if ($existingStatus) {
                $statusStr = $existingStatus['status'] ?? 'unknown';
                $messages = [
                    'pending' => 'Lời mời kết bạn đã được gửi trước đó',
                    'accepted' => 'Hai bạn đã là bạn bè rồi',
                    'blocked' => 'Không thể gửi lời mời kết bạn',
                ];
                return $this->errorResponse($messages[$statusStr] ?? 'Quan hệ đã tồn tại');
            }

            $friendship = Friendship::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'status' => 'pending',
            ]);

            $receiver = User::select('id', 'name', 'avatar')->find($receiverId);

            return $this->successResponse([
                'id' => $friendship->id,
                'user' => [
                    'id' => $receiver->id,
                    'name' => $receiver->name,
                    'avatar_url' => $receiver->avatar_url,
                ],
                'status' => 'pending',
            ], 'Đã gửi lời mời kết bạn', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi gửi lời mời kết bạn: ' . $e->getMessage());
        }
    }

    /**
     * Chấp nhận lời mời kết bạn
     * POST /api/friends/{friendshipId}/accept
     */
    public function acceptRequest(Request $request, int $friendshipId)
    {
        try {
            $friendship = Friendship::find($friendshipId);

            if (!$friendship) {
                return $this->notFoundResponse('Không tìm thấy lời mời kết bạn');
            }

            if ($friendship->receiver_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền chấp nhận lời mời này');
            }

            if ($friendship->status !== 'pending') {
                return $this->errorResponse('Lời mời kết bạn đã được xử lý trước đó');
            }

            $friendship->update(['status' => 'accepted']);

            // Auto-create mutual follow records
            UserFollow::firstOrCreate([
                'follower_id' => $friendship->sender_id,
                'following_id' => $friendship->receiver_id,
            ]);
            UserFollow::firstOrCreate([
                'follower_id' => $friendship->receiver_id,
                'following_id' => $friendship->sender_id,
            ]);

            $sender = User::select('id', 'name', 'avatar')->find($friendship->sender_id);

            return $this->successResponse([
                'id' => $friendship->id,
                'user' => [
                    'id' => $sender->id,
                    'name' => $sender->name,
                    'avatar_url' => $sender->avatar_url,
                ],
                'status' => 'accepted',
            ], 'Đã chấp nhận lời mời kết bạn');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi chấp nhận lời mời: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối lời mời kết bạn
     * POST /api/friends/{friendshipId}/reject
     */
    public function rejectRequest(Request $request, int $friendshipId)
    {
        try {
            $friendship = Friendship::find($friendshipId);

            if (!$friendship) {
                return $this->notFoundResponse('Không tìm thấy lời mời kết bạn');
            }

            if ($friendship->receiver_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền từ chối lời mời này');
            }

            if ($friendship->status !== 'pending') {
                return $this->errorResponse('Lời mời kết bạn đã được xử lý trước đó');
            }

            $friendship->delete();

            return $this->successResponse(null, 'Đã từ chối lời mời kết bạn');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi từ chối lời mời: ' . $e->getMessage());
        }
    }

    /**
     * Hủy lời mời đã gửi
     * DELETE /api/friends/{friendshipId}/cancel
     */
    public function cancelRequest(Request $request, int $friendshipId)
    {
        try {
            $friendship = Friendship::find($friendshipId);

            if (!$friendship) {
                return $this->notFoundResponse('Không tìm thấy lời mời kết bạn');
            }

            if ($friendship->sender_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền hủy lời mời này');
            }

            if ($friendship->status !== 'pending') {
                return $this->errorResponse('Không thể hủy lời mời đã được xử lý');
            }

            $friendship->delete();

            return $this->successResponse(null, 'Đã hủy lời mời kết bạn');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi hủy lời mời: ' . $e->getMessage());
        }
    }

    /**
     * Hủy kết bạn (unfriend)
     * DELETE /api/friends/{userId}/unfriend
     */
    public function unfriend(Request $request, int $userId)
    {
        try {
            $currentUserId = $request->user()->id;

            if (!Friendship::areFriends($currentUserId, $userId)) {
                return $this->errorResponse('Hai bạn không phải là bạn bè');
            }

            // Tìm và xóa friendship (có thể là sender hoặc receiver)
            $friendship = Friendship::where(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
            })->where('status', 'accepted')->first();

            if ($friendship) {
                $friendship->delete();
            }

            return $this->successResponse(null, 'Đã hủy kết bạn');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi hủy kết bạn: ' . $e->getMessage());
        }
    }

    /**
     * Chặn người dùng
     * POST /api/friends/{userId}/block
     */
    public function block(Request $request, int $userId)
    {
        try {
            $currentUserId = $request->user()->id;

            if ($currentUserId === $userId) {
                return $this->errorResponse('Bạn không thể chặn chính mình');
            }

            // Xóa friendship hiện tại nếu có
            Friendship::where(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
            })->orWhere(function ($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
            })->delete();

            // Tạo block
            Friendship::create([
                'sender_id' => $currentUserId,
                'receiver_id' => $userId,
                'status' => 'blocked',
            ]);

            return $this->successResponse(null, 'Đã chặn người dùng');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi chặn người dùng: ' . $e->getMessage());
        }
    }

    /**
     * Bỏ chặn người dùng
     * DELETE /api/friends/{userId}/unblock
     */
    public function unblock(Request $request, int $userId)
    {
        try {
            $currentUserId = $request->user()->id;

            $friendship = Friendship::where('sender_id', $currentUserId)
                ->where('receiver_id', $userId)
                ->where('status', 'blocked')
                ->first();

            if (!$friendship) {
                return $this->errorResponse('Người dùng này không bị chặn');
            }

            $friendship->delete();

            return $this->successResponse(null, 'Đã bỏ chặn người dùng');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi bỏ chặn người dùng: ' . $e->getMessage());
        }
    }

    /**
     * Tìm kiếm người dùng (gợi ý kết bạn)
     * GET /api/friends/search
     */
    public function searchUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:2',
            ], [
                'search.required' => 'Từ khóa tìm kiếm là bắt buộc',
                'search.min' => 'Từ khóa phải có ít nhất 2 ký tự',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = $request->user();
            $search = $request->search;

            $users = User::where('id', '!=', $user->id)
                ->where('role', 'user')
                ->where('is_blocked', false)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->select('id', 'name', 'avatar', 'bio')
                ->limit(20)
                ->get()
                ->map(function ($u) use ($user) {
                    $statusData = Friendship::getStatus($user->id, $u->id);
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'avatar_url' => $u->avatar_url,
                        'bio' => $u->bio,
                        'friendship_status' => $statusData['status'] ?? null,
                    ];
                });

            return $this->successResponse($users, 'Tìm kiếm người dùng thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi tìm kiếm: ' . $e->getMessage());
        }
    }

    /**
     * Xem profile của user khác
     * GET /api/friends/profile/{userId}
     */
    public function profile(Request $request, int $userId)
    {
        try {
            $user = User::select('id', 'name', 'email', 'avatar', 'bio', 'address', 'created_at')
                ->withCount(['bookings', 'reviews', 'blogPosts' => function ($q) {
                    $q->where('status', 'published');
                }])
                ->find($userId);

            if (!$user) {
                return $this->notFoundResponse('Không tìm thấy người dùng');
            }

            $currentUser = $request->user();
            $friendshipData = Friendship::getStatus($currentUser->id, $userId);
            $friendshipStatus = $friendshipData['status'] ?? null;
            $friendIds = Friendship::getFriendIds($userId);

            // Bạn chung
            $currentFriendIds = Friendship::getFriendIds($currentUser->id);
            $mutualFriends = array_intersect($friendIds, $currentFriendIds);

            // Check if current user follows this user
            $isFollowing = UserFollow::where('follower_id', $currentUser->id)
                ->where('following_id', $userId)
                ->exists();

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'address' => $user->address,
                'friends_count' => count($friendIds),
                'bookings_count' => $user->bookings_count,
                'reviews_count' => $user->reviews_count,
                'blog_posts_count' => $user->blog_posts_count,
                'mutual_friends_count' => count($mutualFriends),
                'friendship_status' => $friendshipStatus,
                'friendship_id' => $friendshipData['id'] ?? null,
                'is_friend' => $friendshipStatus === 'accepted',
                'is_following' => $isFollowing,
                'joined_at' => $user->created_at->toISOString(),
            ], 'Lấy thông tin người dùng thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy thông tin: ' . $e->getMessage());
        }
    }

    /**
     * Follow / Unfollow người dùng
     * POST /api/friends/{userId}/follow
     */
    public function toggleFollow(Request $request, int $userId)
    {
        try {
            $user = $request->user();

            if ($user->id === $userId) {
                return $this->errorResponse('Bạn không thể follow chính mình');
            }

            // Check target user exists
            $targetUser = User::find($userId);
            if (!$targetUser) {
                return $this->notFoundResponse('Không tìm thấy người dùng');
            }

            $existing = UserFollow::where('follower_id', $user->id)
                ->where('following_id', $userId)
                ->first();

            if ($existing) {
                $existing->delete();
                return $this->successResponse([
                    'is_following' => false,
                    'user_id' => $userId,
                ], 'Đã bỏ theo dõi người dùng');
            }

            UserFollow::create([
                'follower_id' => $user->id,
                'following_id' => $userId,
            ]);

            return $this->successResponse([
                'is_following' => true,
                'user_id' => $userId,
            ], 'Đã theo dõi người dùng');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi thay đổi trạng thái theo dõi: ' . $e->getMessage());
        }
    }

    /**
     * Gợi ý kết bạn (người dùng cùng tour, có bạn chung)
     * GET /api/friends/suggestions
     */
    public function suggestions(Request $request)
    {
        try {
            $user = $request->user();
            $friendIds = Friendship::getFriendIds($user->id);

            // Lấy danh sách user đã block hoặc bị block
            $blockedIds = Friendship::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })->where('status', 'blocked')->get()->map(function ($f) use ($user) {
                return $f->sender_id === $user->id ? $f->receiver_id : $f->sender_id;
            })->toArray();

            // Lấy user đã gửi/nhận lời mời pending
            $pendingIds = Friendship::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            })->where('status', 'pending')->get()->map(function ($f) use ($user) {
                return $f->sender_id === $user->id ? $f->receiver_id : $f->sender_id;
            })->toArray();

            $excludeIds = array_merge($friendIds, $blockedIds, $pendingIds, [$user->id]);

            // Gợi ý: người dùng mới nhất, chưa kết bạn
            $suggestions = User::whereNotIn('id', $excludeIds)
                ->where('role', 'user')
                ->where('is_blocked', false)
                ->select('id', 'name', 'avatar', 'bio')
                ->inRandomOrder()
                ->limit(10)
                ->get()
                ->map(function ($u) use ($friendIds) {
                    // Đếm bạn chung
                    $uFriendIds = Friendship::getFriendIds($u->id);
                    $mutualCount = count(array_intersect($friendIds, $uFriendIds));

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'avatar_url' => $u->avatar_url,
                        'bio' => $u->bio,
                        'mutual_friends_count' => $mutualCount,
                        'friendship_status' => null,
                    ];
                })
                ->sortByDesc('mutual_friends_count')
                ->values();

            return $this->successResponse($suggestions, 'Lấy gợi ý kết bạn thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy gợi ý: ' . $e->getMessage());
        }
    }
}
