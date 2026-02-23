<?php

namespace App\Http\Controllers\Api;

use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\BlogLike;
use App\Models\Friendship;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ApiBlogController extends ApiController
{
    /**
     * Danh sách bài blog đã publish (trang chủ blog)
     * GET /api/blog
     */
    public function index(Request $request)
    {
        try {
            $query = BlogPost::with(['user:id,name,avatar'])
                ->published()
                ->latest('published_at');

            // Lọc theo danh mục
            if ($request->has('category') && $request->category) {
                $query->byCategory($request->category);
            }

            // Lọc bài nổi bật
            if ($request->has('featured') && $request->featured) {
                $query->featured();
            }

            // Tìm kiếm
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('excerpt', 'like', "%{$search}%");
                });
            }

            // Lọc theo tag
            if ($request->has('tag') && $request->tag) {
                $tag = $request->tag;
                $query->whereJsonContains('tags', $tag);
            }

            // Lọc theo tour liên quan
            if ($request->has('tour_id') && $request->tour_id) {
                $query->where('tour_id', $request->tour_id);
            }

            $perPage = $request->input('per_page', 10);
            $posts = $query->paginate($perPage);

            $data = $posts->map(function ($post) use ($request) {
                return $this->formatPostSummary($post, $request->user());
            });

            return $this->successResponseWithMeta(
                $data,
                $this->getPaginationMeta($posts),
                'Lấy danh sách bài viết thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Chi tiết bài blog
     * GET /api/blog/{slug}
     */
    public function show(Request $request, string $slug)
    {
        try {
            $post = BlogPost::with([
                'user:id,name,avatar,bio',
                'tour:id,name,location,difficulty',
                'comments' => function ($q) {
                    $q->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
                      ->whereNull('parent_id')
                      ->latest()
                      ->limit(20);
                },
            ])->where('slug', $slug)->first();

            if (!$post) {
                return $this->notFoundResponse('Không tìm thấy bài viết');
            }

            // Chỉ cho xem bài đã publish hoặc bài của chính mình
            if ($post->status !== 'published' && $post->user_id !== $request->user()->id) {
                return $this->notFoundResponse('Bài viết không tồn tại hoặc chưa được xuất bản');
            }

            // Tăng lượt xem
            $post->incrementViewCount();

            $data = $this->formatPostDetail($post, $request->user());

            return $this->successResponse($data, 'Lấy chi tiết bài viết thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy chi tiết bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Tạo bài blog mới
     * POST /api/blog
     */
    public function store(Request $request)
    {
        try {
            $isDraft = $request->input('status') === 'draft';

            $rules = [
                'title' => 'required|string|min:5|max:255',
                'content' => $isDraft ? 'nullable|string' : 'required|string|min:20',
                'excerpt' => 'nullable|string|max:500',
                'category' => $isDraft ? 'nullable|in:guide,tips,reviews,stories' : 'required|in:guide,tips,reviews,stories',
                'tags' => 'nullable|array|max:10',
                'tags.*' => 'string|max:50',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                'status' => 'nullable|in:draft,published',
                'tour_id' => 'nullable|exists:tours,id',
            ];

            $validator = Validator::make($request->all(), $rules, [
                'title.required' => 'Tiêu đề là bắt buộc',
                'title.min' => 'Tiêu đề phải có ít nhất 5 ký tự',
                'content.required' => 'Nội dung là bắt buộc',
                'content.min' => 'Nội dung phải có ít nhất 20 ký tự',
                'category.required' => 'Danh mục là bắt buộc',
                'category.in' => 'Danh mục không hợp lệ',
                'cover_image.image' => 'Ảnh bìa phải là file ảnh',
                'cover_image.max' => 'Ảnh bìa không được vượt quá 5MB',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = $request->user();

            // Upload ảnh bìa
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')->store('blog/covers', 'public');
            }

            // Upload ảnh bổ sung
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('blog/images', 'public');
                }
            }

            // Tạo excerpt tự động nếu không có
            $excerpt = $request->excerpt ?: ($request->content ? Str::limit(strip_tags($request->content), 200) : null);

            $status = $request->input('status', 'published');

            $post = BlogPost::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'slug' => BlogPost::generateSlug($request->title),
                'content' => $request->content ?? '',
                'excerpt' => $excerpt,
                'cover_image' => $coverImagePath,
                'images' => $imagePaths ?: null,
                'category' => $request->category ?? 'stories',
                'tags' => $request->tags ?: null,
                'status' => $status,
                'tour_id' => $request->tour_id,
                'published_at' => $status === 'published' ? now() : null,
            ]);

            $post->load('user:id,name,avatar');

            return $this->successResponse(
                $this->formatPostDetail($post, $user),
                'Tạo bài viết thành công',
                201
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi tạo bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật bài blog
     * PUT /api/blog/{id}
     */
    public function update(Request $request, int $id)
    {
        try {
            $post = BlogPost::find($id);

            if (!$post) {
                return $this->notFoundResponse('Không tìm thấy bài viết');
            }

            if ($post->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền chỉnh sửa bài viết này');
            }

            $isDraft = $request->input('status', $post->status) === 'draft';

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|min:5|max:255',
                'content' => $isDraft ? 'nullable|string' : 'sometimes|required|string|min:20',
                'excerpt' => 'nullable|string|max:500',
                'category' => $isDraft ? 'nullable|in:guide,tips,reviews,stories' : 'sometimes|required|in:guide,tips,reviews,stories',
                'tags' => 'nullable|array|max:10',
                'tags.*' => 'string|max:50',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'status' => 'nullable|in:draft,published,archived',
                'tour_id' => 'nullable|exists:tours,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $request->only(['title', 'content', 'excerpt', 'category', 'tags', 'status', 'tour_id']);

            // Cập nhật slug nếu title thay đổi
            if (isset($data['title']) && $data['title'] !== $post->title) {
                $data['slug'] = BlogPost::generateSlug($data['title']);
            }

            // Upload ảnh bìa mới
            if ($request->hasFile('cover_image')) {
                // Xóa ảnh cũ
                if ($post->cover_image) {
                    Storage::disk('public')->delete($post->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('blog/covers', 'public');
            }

            // Xử lý published_at
            if (isset($data['status']) && $data['status'] === 'published' && !$post->published_at) {
                $data['published_at'] = now();
            }

            $post->update($data);
            $post->load('user:id,name,avatar', 'tour:id,name,location');

            return $this->successResponse(
                $this->formatPostDetail($post, $request->user()),
                'Cập nhật bài viết thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Xóa bài blog
     * DELETE /api/blog/{id}
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $post = BlogPost::find($id);

            if (!$post) {
                return $this->notFoundResponse('Không tìm thấy bài viết');
            }

            if ($post->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền xóa bài viết này');
            }

            // Xóa ảnh
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            if ($post->images) {
                foreach ($post->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $post->delete();

            return $this->successResponse(null, 'Xóa bài viết thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Bài viết của bản thân
     * GET /api/blog/my-posts
     */
    public function myPosts(Request $request)
    {
        try {
            $query = BlogPost::with('user:id,name,avatar')
                ->byUser($request->user()->id)
                ->latest();

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $perPage = $request->input('per_page', 10);
            $posts = $query->paginate($perPage);

            $data = $posts->map(function ($post) use ($request) {
                return $this->formatPostSummary($post, $request->user());
            });

            return $this->successResponseWithMeta(
                $data,
                $this->getPaginationMeta($posts),
                'Lấy danh sách bài viết của tôi thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy bài viết của tôi: ' . $e->getMessage());
        }
    }

    /**
     * Bài viết của một user khác
     * GET /api/blog/user/{userId}
     */
    public function userPosts(Request $request, int $userId)
    {
        try {
            $query = BlogPost::with('user:id,name,avatar')
                ->byUser($userId)
                ->published()
                ->latest('published_at');

            $perPage = $request->input('per_page', 10);
            $posts = $query->paginate($perPage);

            $data = $posts->map(function ($post) use ($request) {
                return $this->formatPostSummary($post, $request->user());
            });

            return $this->successResponseWithMeta(
                $data,
                $this->getPaginationMeta($posts),
                'Lấy danh sách bài viết của người dùng thành công'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    /**
     * Like / Unlike bài viết
     * POST /api/blog/{id}/like
     */
    public function toggleLike(Request $request, int $id)
    {
        try {
            $post = BlogPost::find($id);
            if (!$post) {
                return $this->notFoundResponse('Không tìm thấy bài viết');
            }

            $user = $request->user();
            $existing = $post->likes()->where('user_id', $user->id)->first();

            if ($existing) {
                $existing->delete();
                $post->decrement('likes_count');
                $isLiked = false;
                $message = 'Đã bỏ thích bài viết';
            } else {
                $post->likes()->create(['user_id' => $user->id]);
                $post->increment('likes_count');
                $isLiked = true;
                $message = 'Đã thích bài viết';
            }

            return $this->successResponse([
                'is_liked' => $isLiked,
                'likes_count' => $post->fresh()->likes_count,
            ], $message);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi thực hiện like: ' . $e->getMessage());
        }
    }

    /**
     * Bình luận bài viết
     * POST /api/blog/{id}/comment
     */
    public function comment(Request $request, int $id)
    {
        try {
            $post = BlogPost::find($id);
            if (!$post) {
                return $this->notFoundResponse('Không tìm thấy bài viết');
            }

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|min:1|max:1000',
                'parent_id' => 'nullable|exists:blog_comments,id',
            ], [
                'content.required' => 'Nội dung bình luận là bắt buộc',
                'content.max' => 'Bình luận không được vượt quá 1000 ký tự',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Kiểm tra parent_id thuộc cùng bài viết
            if ($request->parent_id) {
                $parentComment = BlogComment::where('id', $request->parent_id)
                    ->where('blog_post_id', $id)
                    ->first();
                if (!$parentComment) {
                    return $this->errorResponse('Bình luận gốc không thuộc bài viết này');
                }
            }

            $comment = BlogComment::create([
                'blog_post_id' => $id,
                'user_id' => $request->user()->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);

            $post->increment('comments_count');

            $comment->load('user:id,name,avatar');

            return $this->successResponse([
                'id' => $comment->id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'likes_count' => 0,
                'is_liked' => false,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar_url' => $comment->user->avatar_url,
                ],
                'replies' => [],
                'created_at' => $comment->created_at->toISOString(),
            ], 'Bình luận thành công', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi bình luận: ' . $e->getMessage());
        }
    }

    /**
     * Xóa bình luận
     * DELETE /api/blog/comment/{commentId}
     */
    public function deleteComment(Request $request, int $commentId)
    {
        try {
            $comment = BlogComment::find($commentId);
            if (!$comment) {
                return $this->notFoundResponse('Không tìm thấy bình luận');
            }

            if ($comment->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('Bạn không có quyền xóa bình luận này');
            }

            $post = $comment->post;

            // Xóa cả replies
            $repliesCount = $comment->replies()->count();
            $comment->replies()->delete();
            $comment->delete();

            // Giảm đếm
            $post->decrement('comments_count', 1 + $repliesCount);

            return $this->successResponse(null, 'Xóa bình luận thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa bình luận: ' . $e->getMessage());
        }
    }

    /**
     * Like / Unlike bình luận
     * POST /api/blog/comment/{commentId}/like
     */
    public function toggleCommentLike(Request $request, int $commentId)
    {
        try {
            $comment = BlogComment::find($commentId);
            if (!$comment) {
                return $this->notFoundResponse('Không tìm thấy bình luận');
            }

            $user = $request->user();
            $existing = $comment->likes()->where('user_id', $user->id)->first();

            if ($existing) {
                $existing->delete();
                $comment->decrement('likes_count');
                $isLiked = false;
            } else {
                $comment->likes()->create(['user_id' => $user->id]);
                $comment->increment('likes_count');
                $isLiked = true;
            }

            return $this->successResponse([
                'is_liked' => $isLiked,
                'likes_count' => $comment->fresh()->likes_count,
            ], $isLiked ? 'Đã thích bình luận' : 'Đã bỏ thích bình luận');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Lỗi khi like bình luận: ' . $e->getMessage());
        }
    }

    /**
     * Danh mục blog với số lượng bài
     * GET /api/blog/categories
     */
    public function categories()
    {
        try {
            $categories = BlogPost::published()
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->get()
                ->map(function ($item) {
                    $labels = [
                        'guide' => 'Hướng dẫn',
                        'tips' => 'Mẹo hay',
                        'reviews' => 'Đánh giá',
                        'stories' => 'Câu chuyện',
                    ];
                    return [
                        'key' => $item->category,
                        'label' => $labels[$item->category] ?? $item->category,
                        'count' => $item->count,
                    ];
                });

            return $this->successResponse($categories, 'Lấy danh mục thành công');
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    // ===== Helpers =====

    private function formatPostSummary(BlogPost $post, $currentUser = null): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'cover_image_url' => $post->cover_image_url,
            'category' => $post->category,
            'category_text' => $post->category_text,
            'tags' => $post->tags ?? [],
            'view_count' => $post->view_count,
            'likes_count' => $post->likes_count,
            'comments_count' => $post->comments_count,
            'reading_time' => $post->reading_time,
            'is_featured' => $post->is_featured,
            'is_liked' => $currentUser ? $post->isLikedBy($currentUser) : false,
            'is_mine' => $currentUser ? $post->user_id === $currentUser->id : false,
            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'avatar_url' => $post->user->avatar_url,
            ],
            'published_at' => $post->published_at?->toISOString(),
            'created_at' => $post->created_at->toISOString(),
        ];
    }

    private function formatPostDetail(BlogPost $post, $currentUser = null): array
    {
        $data = $this->formatPostSummary($post, $currentUser);
        $data['content'] = Str::markdown($post->content);
        $data['images'] = $post->images ? array_map(function ($img) {
            return asset('storage/' . $img);
        }, $post->images) : [];
        $data['status'] = $post->status;
        $data['tour'] = $post->tour ? [
            'id' => $post->tour->id,
            'name' => $post->tour->name,
            'location' => $post->tour->location ?? null,
            'difficulty' => $post->tour->difficulty ?? null,
        ] : null;

        // Comments
        if ($post->relationLoaded('comments')) {
            $data['comments'] = $post->comments->map(function ($comment) use ($currentUser) {
                return $this->formatComment($comment, $currentUser);
            });
        }

        $data['user']['bio'] = $post->user->bio ?? null;

        return $data;
    }

    private function formatComment(BlogComment $comment, $currentUser = null): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'parent_id' => $comment->parent_id,
            'likes_count' => $comment->likes_count,
            'is_liked' => $currentUser ? $comment->isLikedBy($currentUser) : false,
            'is_mine' => $currentUser ? $comment->user_id === $currentUser->id : false,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn($r) => $this->formatComment($r, $currentUser))
                : [],
            'created_at' => $comment->created_at->toISOString(),
        ];
    }
}
