<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ApiAuthController extends ApiController
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required' => 'Vui lòng nhập họ tên',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã được sử dụng',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            // Create token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toIso8601String(),
            ], 'Đăng ký thành công', 201);

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi đăng ký: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Attempt login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse(
                'Email hoặc mật khẩu không chính xác',
                null,
                'INVALID_CREDENTIALS',
                401
            );
        }

        $user = Auth::user();

        // Check if user is blocked
        if ($user->is_blocked) {
            Auth::logout();
            return $this->errorResponse(
                'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                null,
                'ACCOUNT_BLOCKED',
                403
            );
        }

        // Revoke all previous tokens (optional - for single device login)
        // $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(30)->toIso8601String(),
        ], 'Đăng nhập thành công');
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        try {
            // Revoke current token
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $request->user()->tokens()->where('id', $token->id)->delete();
            }

            return $this->successResponse(null, 'Đăng xuất thành công');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi đăng xuất');
        }
    }

    /**
     * Refresh token (optional - revoke old and create new)
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();

            // Revoke current token
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $request->user()->tokens()->where('id', $token->id)->delete();
            }

            // Create new token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toIso8601String(),
            ], 'Token đã được làm mới');

        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi làm mới token');
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        return $this->successResponse(
            new UserResource($request->user()),
            'Lấy thông tin user thành công'
        );
    }

    /**
     * Forgot password (send reset link)
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.exists' => 'Email không tồn tại trong hệ thống',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // TODO: Implement password reset logic with email
        // For now, just return success
        return $this->successResponse(
            null,
            'Đã gửi link đặt lại mật khẩu đến email của bạn'
        );
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
            'token' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // TODO: Implement password reset logic
        return $this->successResponse(
            null,
            'Đặt lại mật khẩu thành công'
        );
    }
}
