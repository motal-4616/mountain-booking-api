<?php

namespace App\Http\Controllers\Api;

use App\Models\Tour;
use App\Models\Wishlist;
use App\Http\Resources\TourResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTourController extends ApiController
{
    /**
     * Get list of tours with filters and pagination
     */
    public function index(Request $request)
    {
        $query = Tour::where('is_active', true);

        // Filter by difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } else {
            $query->orderBy('id', $sortOrder);
        }

        // Load relationships - chỉ lấy reviews đã approved
        $query->with(['reviews' => function($q) {
            $q->where('status', 'approved');
        }, 'schedules' => function($q) {
            $q->where('is_active', true)
              ->where('departure_date', '>=', now())
              ->where('available_slots', '>', 0)
              ->orderBy('departure_date');
        }]);

        // Check wishlist status for authenticated users (dùng guard sanctum trực tiếp vì route public không có middleware auth:sanctum)
        $authUser = $request->user('sanctum');
        if ($authUser) {
            $userId = $authUser->id;
            $query->addSelect(['is_wishlisted' => Wishlist::selectRaw('1')
                ->whereColumn('tour_id', 'tours.id')
                ->where('user_id', $userId)
                ->limit(1)
            ]);
        }

        // Paginate
        $perPage = $request->get('per_page', 15);
        $tours = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            TourResource::collection($tours),
            $this->getPaginationMeta($tours),
            'Lấy danh sách tours thành công'
        );
    }

    /**
     * Get featured tours
     */
    public function featured(Request $request)
    {
        $limit = $request->get('limit', 6);

        $tours = Tour::where('is_active', true)
            ->with(['reviews' => function($q) {
                $q->where('status', 'approved');
            }, 'schedules' => function($q) {
                $q->where('is_active', true)
                  ->where('departure_date', '>=', now())
                  ->where('available_slots', '>', 0)
                  ->orderBy('departure_date');
            }])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return $this->successResponse(
            TourResource::collection($tours),
            'Lấy tours nổi bật thành công'
        );
    }

    /**
     * Get popular tours (by reviews count/rating)
     */
    public function popular(Request $request)
    {
        $limit = $request->get('limit', 6);

        $tours = Tour::where('is_active', true)
            ->withCount(['reviews' => function($q) {
                $q->where('status', 'approved');
            }])
            ->with(['reviews' => function($q) {
                $q->where('status', 'approved');
            }, 'schedules' => function($q) {
                $q->where('is_active', true)
                  ->where('departure_date', '>=', now())
                  ->where('available_slots', '>', 0)
                  ->orderBy('departure_date');
            }])
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();

        return $this->successResponse(
            TourResource::collection($tours),
            'Lấy tours phổ biến thành công'
        );
    }

    /**
     * Get tour details
     */
    public function show(Tour $tour)
    {
        if (!$tour->is_active) {
            return $this->notFoundResponse('Tour không tồn tại hoặc đã bị ẩn');
        }

        // Load relationships
        $tour->load([
            'reviews' => function($q) {
                $q->where('status', 'approved')
                  ->with('user')
                  ->orderBy('created_at', 'desc');
            },
            'schedules' => function($q) {
                $q->where('is_active', true)
                  ->where('departure_date', '>=', now())
                  ->orderBy('departure_date');
            }
        ]);

        // Check wishlist status for authenticated users (dùng guard sanctum trực tiếp vì route public)
        $authUser = request()->user('sanctum');
        if ($authUser) {
            $tour->is_wishlisted = Wishlist::where('tour_id', $tour->id)
                ->where('user_id', $authUser->id)
                ->exists();
        }

        return $this->successResponse(
            new TourResource($tour),
            'Lấy chi tiết tour thành công'
        );
    }

    /**
     * Get schedules of a tour
     */
    public function getSchedules(Request $request, Tour $tour)
    {
        if (!$tour->is_active) {
            return $this->notFoundResponse('Tour không tồn tại');
        }

        $query = $tour->schedules()
            ->where('is_active', true)
            ->where('departure_date', '>=', now())
            ->orderBy('departure_date');

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('departure_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('departure_date', '<=', $request->to_date);
        }

        // Filter available only
        if ($request->get('available_only', false)) {
            $query->where('available_slots', '>', 0);
        }

        $schedules = $query->get();

        return $this->successResponse(
            ScheduleResource::collection($schedules),
            'Lấy lịch khởi hành thành công'
        );
    }

    /**
     * Get reviews of a tour
     */
    public function getReviews(Request $request, Tour $tour)
    {
        if (!$tour->is_active) {
            return $this->notFoundResponse('Tour không tồn tại');
        }

        $query = $tour->reviews()
            ->where('status', 'approved')
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Filter by rating
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        $perPage = $request->get('per_page', 10);
        $reviews = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            ReviewResource::collection($reviews),
            $this->getPaginationMeta($reviews),
            'Lấy đánh giá thành công'
        );
    }

    /**
     * Search tours
     */
    public function search(Request $request)
    {
        $keyword = $request->get('q', '');

        if (empty($keyword)) {
            return $this->errorResponse('Vui lòng nhập từ khóa tìm kiếm', null, 'MISSING_KEYWORD', 400);
        }

        $query = Tour::where('is_active', true)
            ->where(function($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('location', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            })
            ->with(['reviews' => function($q) {
                $q->where('status', 'approved');
            }, 'schedules' => function($q) {
                $q->where('is_active', true)
                  ->where('departure_date', '>=', now())
                  ->where('available_slots', '>', 0)
                  ->orderBy('departure_date');
            }]);

        $perPage = $request->get('per_page', 15);
        $tours = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            TourResource::collection($tours),
            $this->getPaginationMeta($tours),
            'Tìm kiếm thành công'
        );
    }
}
