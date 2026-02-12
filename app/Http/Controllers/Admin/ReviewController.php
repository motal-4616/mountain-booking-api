<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'tour', 'booking']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Search by tour name or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('tour', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'avg_rating' => round(Review::where('status', 'approved')->avg('rating'), 1),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Hiển thị chi tiết đánh giá
     */
    public function show($id)
    {
        $review = Review::with(['user', 'tour', 'booking.schedule'])->findOrFail($id);
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Cập nhật trạng thái đánh giá
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $review = Review::findOrFail($id);
        $review->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? $review->admin_note,
        ]);

        $statusText = match($validated['status']) {
            'approved' => 'duyệt',
            'rejected' => 'từ chối',
            'pending' => 'chuyển về chờ duyệt',
        };

        return redirect()->back()->with('success', "Đã {$statusText} đánh giá thành công.");
    }

    /**
     * Xóa đánh giá
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Đã xóa đánh giá thành công.');
    }

    /**
     * Bulk action
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'review_ids' => 'required|array',
            'review_ids.*' => 'exists:reviews,id',
        ]);

        $count = 0;
        
        switch ($validated['action']) {
            case 'approve':
                $count = Review::whereIn('id', $validated['review_ids'])
                    ->update(['status' => 'approved']);
                return redirect()->back()->with('success', "Đã duyệt {$count} đánh giá.");
                
            case 'reject':
                $count = Review::whereIn('id', $validated['review_ids'])
                    ->update(['status' => 'rejected']);
                return redirect()->back()->with('success', "Đã từ chối {$count} đánh giá.");
                
            case 'delete':
                $count = Review::whereIn('id', $validated['review_ids'])->delete();
                return redirect()->back()->with('success', "Đã xóa {$count} đánh giá.");
        }

        return redirect()->back();
    }
}
