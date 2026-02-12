<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware kiểm tra quyền Super Admin
 * Chỉ super_admin mới được truy cập
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        if (!$user->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền truy cập chức năng này. Yêu cầu quyền Super Admin.');
        }

        return $next($request);
    }
}
