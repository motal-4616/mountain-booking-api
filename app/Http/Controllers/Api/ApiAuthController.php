<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
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
     * Forgot password - send OTP code
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

        $email = $request->email;

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in password_reset_tokens table (hash for security)
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        // Try sending email (best effort - may fail on some hosting)
        $emailSent = false;
        try {
            Mail::send('emails.reset-password', ['otp' => $otp], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Mã OTP đặt lại mật khẩu - Mountain Booking');
            });
            $emailSent = true;
            Log::info("Password reset OTP sent to: {$email}");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$email}: " . $e->getMessage());
        }

        return $this->successResponse(
            [
                'otp' => $otp,
                'email_sent' => $emailSent,
                'expires_in' => 60,
            ],
            $emailSent
                ? 'Mã OTP đã được gửi đến email của bạn.'
                : 'Mã OTP đã được tạo.'
        );
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'email.required' => 'Vui lòng nhập email',
            'otp.required' => 'Vui lòng nhập mã OTP',
            'otp.size' => 'Mã OTP phải gồm 6 chữ số',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        // Find token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return $this->errorResponse(
                'Mã OTP không hợp lệ hoặc đã hết hạn.',
                null, 'INVALID_OTP', 400
            );
        }

        // Check expiry (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->errorResponse(
                'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.',
                null, 'OTP_EXPIRED', 400
            );
        }

        // Verify OTP
        if (!Hash::check($request->otp, $record->token)) {
            return $this->errorResponse(
                'Mã OTP không chính xác.',
                null, 'WRONG_OTP', 400
            );
        }

        // Reset password
        $user = User::where('email', $request->email)->first();
        $user->forceFill(['password' => Hash::make($request->password)])->save();
        $user->tokens()->delete();

        // Clean up token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->successResponse(null, 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập lại.');
    }
}
