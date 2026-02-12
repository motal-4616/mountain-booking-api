<?php

namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use App\Models\Tour;
use App\Http\Resources\WishlistResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiWishlistController extends ApiController
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $query = Auth::user()->wishlists()
            ->with(['tour' => function($q) {
                $q->with(['reviews', 'schedules' => function($sq) {
                    $sq->where('is_active', true)
                       ->where('departure_date', '>=', now())
                       ->where('available_slots', '>', 0)
                       ->orderBy('departure_date');
                }]);
            }])
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 15);
        $wishlists = $query->paginate($perPage);

        return $this->successResponseWithMeta(
            WishlistResource::collection($wishlists),
            $this->getPaginationMeta($wishlists),
            'Lấy danh sách wishlist thành công'
        );
    }

    /**
     * Toggle tour in wishlist
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'tour_id' => ['required', 'exists:tours,id'],
        ]);

        try {
            $tour = Tour::findOrFail($request->tour_id);

            if (!$tour->is_active) {
                return $this->errorResponse(
                    'Tour không còn hoạt động',
                    null,
                    'TOUR_INACTIVE',
                    400
                );
            }

            $wishlist = Wishlist::where('user_id', Auth::id())
                ->where('tour_id', $request->tour_id)
                ->first();

            if ($wishlist) {
                // Remove from wishlist
                $wishlist->delete();
                return $this->successResponse([
                    'is_wishlisted' => false,
                    'tour_id' => $request->tour_id,
                ], 'Đã xóa khỏi danh sách yêu thích');
            } else {
                // Add to wishlist
                $wishlist = Wishlist::create([
                    'user_id' => Auth::id(),
                    'tour_id' => $request->tour_id,
                ]);

                return $this->successResponse([
                    'is_wishlisted' => true,
                    'tour_id' => $request->tour_id,
                    'wishlist_id' => $wishlist->id,
                ], 'Đã thêm vào danh sách yêu thích');
            }

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Check if tour is in wishlist
     */
    public function check(Tour $tour)
    {
        $isWishlisted = Wishlist::where('user_id', Auth::id())
            ->where('tour_id', $tour->id)
            ->exists();

        return $this->successResponse([
            'is_wishlisted' => $isWishlisted,
            'tour_id' => $tour->id,
        ], 'Kiểm tra wishlist thành công');
    }
}
