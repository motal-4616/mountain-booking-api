<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Controller quản lý Tour (Admin)
 */
class TourController extends Controller
{
    /**
     * Danh sách tour với bộ lọc
     */
    public function index(Request $request)
    {
        // Query với bộ lọc
        $query = Tour::select(['id', 'name', 'image', 'difficulty', 'location', 'is_active', 'created_at'])
            ->withCount('schedules');

        // Tìm kiếm theo tên hoặc địa điểm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Lọc theo độ khó
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $tours = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.tours.index', compact('tours'));
    }

    /**
     * Form thêm tour mới
     */
    public function create()
    {
        return view('admin.tours.create');
    }

    /**
     * Lưu tour mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'overview' => 'nullable|string',
            'itinerary' => 'nullable|array',
            'includes' => 'nullable|string',
            'excludes' => 'nullable|string',
            'highlights' => 'nullable|string',
            'altitude' => 'nullable|integer|min:0',
            'best_time' => 'nullable|string|max:255',
            'map_lat' => 'nullable|numeric|between:-90,90',
            'map_lng' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên tour.',
            'difficulty.required' => 'Vui lòng chọn độ khó.',
            'location.required' => 'Vui lòng nhập địa điểm.',
            'image.image' => 'File phải là hình ảnh.',
            'image.max' => 'Kích thước ảnh tối đa 2MB.',
        ]);

        // Kiểm tra trùng lặp tour name + location
        $exists = Tour::where('name', $request->name)
            ->where('location', $request->location)
            ->exists();
        
        if ($exists) {
            return back()->withInput()
                ->with('error', 'Tour với tên và địa điểm này đã tồn tại.');
        }

        $data = $request->except(['image', 'gallery']);
        $data['is_active'] = $request->has('is_active');

        // Xử lý upload ảnh chính
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/tours'), $imageName);
            $data['image'] = 'uploads/tours/' . $imageName;
        }

        // Xử lý upload gallery
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/tours/gallery'), $fileName);
                $galleryPaths[] = 'uploads/tours/gallery/' . $fileName;
            }
            $data['gallery'] = $galleryPaths;
        }

        // Xử lý itinerary từ form - array đơn giản
        if ($request->has('itinerary')) {
            $itinerary = array_filter($request->input('itinerary', []), function($item) {
                return !empty(trim($item));
            });
            $data['itinerary'] = !empty($itinerary) ? array_values($itinerary) : null;
        }

        Tour::create($data);

        return redirect()->route('admin.tours.index')
            ->with('success', 'Thêm tour thành công!');
    }

    /**
     * Xem chi tiết tour
     */
    public function show(Tour $tour)
    {
        $tour->load(['schedules' => function($query) {
            $query->orderBy('departure_date', 'desc');
        }]);
        
        return view('admin.tours.show', compact('tour'));
    }

    /**
     * Form sửa tour
     */
    public function edit(Tour $tour)
    {
        return view('admin.tours.edit', compact('tour'));
    }

    /**
     * Cập nhật tour
     */
    public function update(Request $request, Tour $tour)
    {
        // Kiểm tra trùng lặp tour name + location (trừ tour hiện tại)
        $exists = Tour::where('name', $request->name)
            ->where('location', $request->location)
            ->where('id', '!=', $tour->id)
            ->exists();
        
        if ($exists) {
            return back()->withInput()
                ->with('error', 'Tour với tên và địa điểm này đã tồn tại.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'overview' => 'nullable|string',
            'itinerary' => 'nullable|array',
            'includes' => 'nullable|string',
            'excludes' => 'nullable|string',
            'highlights' => 'nullable|string',
            'altitude' => 'nullable|integer|min:0',
            'best_time' => 'nullable|string|max:255',
            'map_lat' => 'nullable|numeric|between:-90,90',
            'map_lng' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->except(['image', 'gallery', 'delete_gallery']);
        $data['is_active'] = $request->has('is_active');

        // Xử lý upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($tour->image && file_exists(public_path($tour->image))) {
                unlink(public_path($tour->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/tours'), $imageName);
            $data['image'] = 'uploads/tours/' . $imageName;
        }

        // Xử lý xóa ảnh gallery
        $currentGallery = $tour->gallery ?? [];
        if ($request->has('delete_gallery')) {
            foreach ($request->input('delete_gallery', []) as $deleteImage) {
                if (file_exists(public_path($deleteImage))) {
                    unlink(public_path($deleteImage));
                }
                $currentGallery = array_values(array_diff($currentGallery, [$deleteImage]));
            }
        }

        // Xử lý upload gallery mới
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $fileName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/tours/gallery'), $fileName);
                $currentGallery[] = 'uploads/tours/gallery/' . $fileName;
            }
        }
        $data['gallery'] = !empty($currentGallery) ? array_values($currentGallery) : null;

        // Xử lý itinerary từ form - array đơn giản
        if ($request->has('itinerary')) {
            $itinerary = array_filter($request->input('itinerary', []), function($item) {
                return !empty(trim($item));
            });
            $data['itinerary'] = !empty($itinerary) ? array_values($itinerary) : null;
        }

        // Track changes để gửi thông báo
        $changes = [];
        if (isset($data['is_active']) && $tour->is_active != $data['is_active']) {
            $changes['is_active'] = $data['is_active'];
        }

        $tour->update($data);

        // Gửi thông báo nếu có thay đổi quan trọng
        if (!empty($changes)) {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyTourUpdated($tour, $changes);
        }

        return redirect()->route('admin.tours.index')
            ->with('success', 'Cập nhật tour thành công!');
    }

    /**
     * Xóa tour
     */
    public function destroy(Tour $tour)
    {
        // Xóa ảnh nếu có
        if ($tour->image && file_exists(public_path($tour->image))) {
            unlink(public_path($tour->image));
        }

        $tour->delete();

        return redirect()->route('admin.tours.index')
            ->with('success', 'Xóa tour thành công!');
    }

    /**
     * Xử lý hành động hàng loạt
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
            'action' => 'required|in:activate,deactivate,delete',
        ]);

        $ids = explode(',', $request->ids);
        $count = 0;

        switch ($request->action) {
            case 'activate':
                $count = Tour::whereIn('id', $ids)->update(['is_active' => true]);
                return redirect()->route('admin.tours.index')
                    ->with('success', "Kích hoạt thành công {$count} tour!");

            case 'deactivate':
                $count = Tour::whereIn('id', $ids)->update(['is_active' => false]);
                return redirect()->route('admin.tours.index')
                    ->with('success', "Tạm dừng thành công {$count} tour!");

            case 'delete':
                // Xóa ảnh của các tour
                $tours = Tour::whereIn('id', $ids)->get();
                foreach ($tours as $tour) {
                    if ($tour->image && file_exists(public_path($tour->image))) {
                        unlink(public_path($tour->image));
                    }
                }
                $count = Tour::whereIn('id', $ids)->delete();
                return redirect()->route('admin.tours.index')
                    ->with('success', "Xóa thành công {$count} tour!");

            default:
                return back()->with('error', 'Hành động không hợp lệ!');
        }
    }
}
