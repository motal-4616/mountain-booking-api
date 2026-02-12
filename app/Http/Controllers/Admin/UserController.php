<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * Controller quản lý User (Admin)
 */
class UserController extends Controller
{
    /**
     * Danh sách user
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Tìm kiếm - sử dụng full text search nếu có nhiều dữ liệu
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo role - sử dụng index
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Sử dụng select để chỉ lấy các trường cần thiết (giảm memory)
        $users = $query->select(['id', 'name', 'email', 'phone', 'role', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Xem chi tiết user
     */
    public function show(User $user)
    {
        $user->load('bookings.schedule.tour');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Hiển thị form tạo user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Lưu user mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,super_admin,booking_manager,content_manager',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Thêm người dùng thành công!');
    }

    /**
     * Hiển thị form sửa user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Cập nhật user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:user,super_admin,booking_manager,content_manager',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công!');
    }

    /**
     * Khóa/Mở khóa user
     */
    public function toggleBlock(User $user)
    {
        // Không cho khóa chính mình
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Không thể khóa tài khoản của chính bạn!');
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $message = $user->is_blocked 
            ? 'Đã khóa người dùng thành công!' 
            : 'Đã mở khóa người dùng thành công!';

        return back()->with('success', $message);
    }

    /**
     * Xóa user
     */
    public function destroy(User $user)
    {
        // Không cho xóa chính mình
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa người dùng thành công!');
    }
}
