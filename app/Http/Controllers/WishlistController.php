<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist.
     */
    public function index()
    {
        $wishlists = Auth::user()->wishlists()
            ->with('tour')
            ->latest()
            ->paginate(12);

        return view('user.wishlist', compact('wishlists'));
    }

    /**
     * Toggle tour in wishlist (add/remove).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|exists:tours,id',
        ]);

        $user = Auth::user();
        $tourId = $request->tour_id;

        $existing = Wishlist::where('user_id', $user->id)
            ->where('tour_id', $tourId)
            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
            $message = 'Đã xóa khỏi danh sách yêu thích';
        } else {
            Wishlist::create([
                'user_id' => $user->id,
                'tour_id' => $tourId,
            ]);
            $action = 'added';
            $message = 'Đã thêm vào danh sách yêu thích';
        }

        $count = Wishlist::where('user_id', $user->id)->count();

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'count' => $count,
        ]);
    }

    /**
     * Sync localStorage wishlist with database.
     */
    public function sync(Request $request)
    {
        $user = Auth::user();
        
        // Get user's current wishlist from database
        $dbWishlist = Wishlist::where('user_id', $user->id)
            ->pluck('tour_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'wishlist' => $dbWishlist,
            'count' => count($dbWishlist),
        ]);
    }
}
