<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ApiProfileController extends ApiController
{
    /**
     * Get user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        return $this->successResponse(
            new UserResource($user),
            'Lấy thông tin profile thành công'
        );
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hôm nay',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'bio' => $request->bio,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
            ]);

            return $this->successResponse(
                new UserResource($user->fresh()),
                'Cập nhật profile thành công'
            );

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi cập nhật profile: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(
                'Mật khẩu hiện tại không chính xác',
                ['current_password' => ['Mật khẩu không chính xác']],
                'INVALID_PASSWORD',
                400
            );
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Optionally revoke all tokens except current
            // $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return $this->successResponse(null, 'Đổi mật khẩu thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi đổi mật khẩu');
        }
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'avatar.required' => 'Vui lòng chọn ảnh',
            'avatar.image' => 'File phải là ảnh',
            'avatar.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'avatar.max' => 'Kích thước ảnh tối đa 2MB',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user = Auth::user();

            // Delete old avatar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            $user->update([
                'avatar' => $avatarPath,
            ]);

            return $this->successResponse([
                'avatar_url' => $user->avatar_url,
            ], 'Cập nhật avatar thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi cập nhật avatar: ' . $e->getMessage());
        }
    }
}
