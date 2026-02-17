<?php

namespace App\Http\Controllers\Api;

use App\Models\Journal;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ApiJournalController extends ApiController
{
    /**
     * Danh sách nhật ký (của mình + feed bạn bè)
     * GET /api/journals
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $query = Journal::with(['user:id,name,avatar', 'tour:id,name'])
                ->visibleTo($user->id)
                ->latest();

            // Lọc theo mood
            if ($request->has('mood') && $request->mood) {
                $query->where('mood', $request->mood);
            }

            // Lọc chỉ bài của mình
            if ($request->has('mine') && $request->mine) {
                $query->byUser($user->id);
            }

            // Lọc theo tour
            if ($request->has('tour_id') && $request->tour_id) {
                $query->where('tour_id', $request->tour_id);
            }

            $perPage = $request->input('per_page', 10);
            $journals = $query->paginate($perPage);

            $data = $journals->map(function ($journal) use ($user) {
                return $this->formatJournal($journal, $user);
            });

            return $this->successResponseWithMeta(
                $data,
                $this->getPaginationMeta($journals),
                'Lấy danh sách nhật ký thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách nhật ký: ' . $e->getMessage());
        }
    }

    /**
     * Nhật ký của bản thân
     * GET /api/journals/my
     */
    public function myJournals(Request $request)
    {
        try {
            $user = $request->user();

            $query = Journal::with(['tour:id,name'])
                ->byUser($user->id)
                ->latest();

            // Lọc theo mood
            if ($request->has('mood') && $request->mood) {
                $query->where('mood', $request->mood);
            }

            // Lọc theo privacy
            if ($request->has('privacy') && $request->privacy) {
                $query->where('privacy', $request->privacy);
            }

            $perPage = $request->input('per_page', 10);
            $journals = $query->paginate($perPage);

            $data = $journals->map(function ($journal) use ($user) {
                return $this->formatJournal($journal, $user);
            });

            return $this->successResponseWithMeta(
                $data,
                $this->getPaginationMeta($journals),
                'Lấy danh sách nhật ký của tôi thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy nhật ký của tôi: ' . $e->getMessage());
        }
    }

    /**
     * Chi tiết nhật ký
     * GET /api/journals/{id}
     */
    public function show(Request $request, int $id)
    {
        try {
            $journal = Journal::with(['user:id,name,avatar,bio', 'tour:id,name,location,difficulty'])
                ->find($id);

            if (!$journal) {
                return $this->notFoundResponse('Không tìm thấy nhật ký');
            }

            $user = $request->user();

            // Kiểm tra quyền xem
            if (!$this->canViewJournal($journal, $user)) {
                return $this->forbiddenResponse('Bạn không có quyền xem nhật ký này');
            }

            return $this->successResponse(
                $this->formatJournalDetail($journal, $user),
                'Lấy chi tiết nhật ký thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy chi tiết nhật ký: ' . $e->getMessage());
        }
    }

    /**
     * Tạo nhật ký mới
     * POST /api/journals
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|min:3|max:255',
                'content' => 'required|string|min:10',
                'mood' => 'required|in:happy,excited,peaceful,tired,sad,challenged',
                'weather' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:200',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'altitude' => 'nullable|numeric|between:-500,10000',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                'privacy' => 'nullable|in:private,friends,public',
                'tour_id' => 'nullable|exists:tours,id',
            ], [
                'title.required' => 'Tiêu đề là bắt buộc',
                'title.min' => 'Tiêu đề phải có ít nhất 3 ký tự',
                'content.required' => 'Nội dung là bắt buộc',
                'content.min' => 'Nội dung phải có ít nhất 10 ký tự',
                'mood.required' => 'Tâm trạng là bắt buộc',
                'mood.in' => 'Tâm trạng không hợp lệ',
                'images.*.max' => 'Mỗi ảnh không được vượt quá 5MB',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = $request->user();

            // Upload ảnh
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('journals/images', 'public');
                }
            }

            $journal = Journal::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'mood' => $request->mood,
                'weather' => $request->weather,
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'altitude' => $request->altitude,
                'images' => $imagePaths ?: null,
                'privacy' => $request->input('privacy', 'private'),
                'tour_id' => $request->tour_id,
            ]);

            $journal->load('tour:id,name');

            return $this->successResponse(
                $this->formatJournalDetail($journal, $user),
                'Tạo nhật ký thành công',
                201
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi tạo nhật ký: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật nhật ký
     * PUT /api/journals/{id}
     */
    public function update(Request $request, int $id)
    {
        try {
            $journal = Journal::find($id);

            if (!$journal) {
                return $this->notFoundResponse('Không tìm thấy nhật ký');
            }

            if ($journal->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền chỉnh sửa nhật ký này');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|min:3|max:255',
                'content' => 'sometimes|required|string|min:10',
                'mood' => 'sometimes|required|in:happy,excited,peaceful,tired,sad,challenged',
                'weather' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:200',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'altitude' => 'nullable|numeric|between:-500,10000',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                'privacy' => 'sometimes|in:private,friends,public',
                'tour_id' => 'nullable|exists:tours,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $request->only([
                'title', 'content', 'mood', 'weather', 'location',
                'latitude', 'longitude', 'altitude', 'privacy', 'tour_id',
            ]);

            // Upload ảnh mới nếu có
            if ($request->hasFile('images')) {
                // Xóa ảnh cũ
                if ($journal->images) {
                    foreach ($journal->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('journals/images', 'public');
                }
                $data['images'] = $imagePaths;
            }

            $journal->update($data);
            $journal->load('user:id,name,avatar', 'tour:id,name');

            return $this->successResponse(
                $this->formatJournalDetail($journal, $request->user()),
                'Cập nhật nhật ký thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật nhật ký: ' . $e->getMessage());
        }
    }

    /**
     * Xóa nhật ký
     * DELETE /api/journals/{id}
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $journal = Journal::find($id);

            if (!$journal) {
                return $this->notFoundResponse('Không tìm thấy nhật ký');
            }

            if ($journal->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền xóa nhật ký này');
            }

            // Xóa ảnh
            if ($journal->images) {
                foreach ($journal->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $journal->delete();

            return $this->successResponse(null, 'Xóa nhật ký thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa nhật ký: ' . $e->getMessage());
        }
    }

    /**
     * Thống kê nhật ký (mood distribution, frequency)
     * GET /api/journals/stats
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();

            // Phân bổ mood
            $moodStats = Journal::byUser($user->id)
                ->selectRaw('mood, count(*) as count')
                ->groupBy('mood')
                ->get()
                ->map(function ($item) {
                    $journal = new Journal();
                    $journal->mood = $item->mood;
                    return [
                        'mood' => $item->mood,
                        'emoji' => $journal->mood_emoji,
                        'text' => $journal->mood_text,
                        'count' => $item->count,
                    ];
                });

            // Tổng nhật ký
            $totalJournals = Journal::byUser($user->id)->count();

            // Nhật ký tháng này
            $thisMonth = Journal::byUser($user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Streak (ngày liên tiếp có viết nhật ký)
            $streak = $this->calculateStreak($user->id);

            return $this->successResponse([
                'total_journals' => $totalJournals,
                'this_month' => $thisMonth,
                'streak' => $streak,
                'mood_distribution' => $moodStats,
            ], 'Lấy thống kê nhật ký thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy thống kê: ' . $e->getMessage());
        }
    }

    // ===== Helpers =====

    private function canViewJournal(Journal $journal, $user): bool
    {
        // Bài của mình luôn xem được
        if ($journal->user_id === $user->id) {
            return true;
        }

        // Public ai cũng xem được
        if ($journal->privacy === 'public') {
            return true;
        }

        // Friends chỉ bạn bè mới xem
        if ($journal->privacy === 'friends') {
            return Friendship::areFriends($user->id, $journal->user_id);
        }

        // Private: chỉ chủ sở hữu
        return false;
    }

    private function formatJournal(Journal $journal, $currentUser): array
    {
        return [
            'id' => $journal->id,
            'title' => $journal->title,
            'content' => \Illuminate\Support\Str::limit($journal->content, 200),
            'mood' => $journal->mood,
            'mood_emoji' => $journal->mood_emoji,
            'mood_text' => $journal->mood_text,
            'weather' => $journal->weather,
            'location' => $journal->location,
            'privacy' => $journal->privacy,
            'privacy_text' => $journal->privacy_text,
            'privacy_icon' => $journal->privacy_icon,
            'has_images' => !empty($journal->images),
            'images_count' => $journal->images ? count($journal->images) : 0,
            'first_image_url' => $journal->images
                ? asset('storage/' . $journal->images[0])
                : null,
            'tour' => $journal->tour ? [
                'id' => $journal->tour->id,
                'name' => $journal->tour->name,
            ] : null,
            'is_mine' => $journal->user_id === $currentUser->id,
            'user' => [
                'id' => $journal->user->id,
                'name' => $journal->user->name,
                'avatar_url' => $journal->user->avatar_url,
            ],
            'created_at' => $journal->created_at->toISOString(),
        ];
    }

    private function formatJournalDetail(Journal $journal, $currentUser): array
    {
        $data = $this->formatJournal($journal, $currentUser);
        $data['content'] = $journal->content; // Full content thay vì truncated
        $data['latitude'] = $journal->latitude;
        $data['longitude'] = $journal->longitude;
        $data['altitude'] = $journal->altitude;
        $data['images'] = $journal->images ? array_map(function ($img) {
            return asset('storage/' . $img);
        }, $journal->images) : [];

        if ($journal->relationLoaded('user') && $journal->user) {
            $data['user']['bio'] = $journal->user->bio ?? null;
        }

        return $data;
    }

    private function calculateStreak(int $userId): int
    {
        $dates = Journal::byUser($userId)
            ->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(365)
            ->pluck('date')
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $streak = 0;
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        // Kiểm tra bắt đầu từ hôm nay hoặc hôm qua
        if ($dates[0] !== $today && $dates[0] !== $yesterday) {
            return 0;
        }

        $currentDate = \Carbon\Carbon::parse($dates[0]);
        foreach ($dates as $date) {
            if ($currentDate->format('Y-m-d') === $date) {
                $streak++;
                $currentDate = $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }
}
