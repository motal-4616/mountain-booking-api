<?php

namespace App\Http\Controllers\Api;

use App\Models\UserLevel;
use App\Services\UserLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiLevelController extends ApiController
{
    protected $levelService;

    public function __construct(UserLevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * Lấy tất cả levels và thông tin ưu đãi
     */
    public function index()
    {
        $levels = UserLevel::getAllLevels();

        return $this->successResponse(
            $levels->map(function ($level) {
                return [
                    'level' => $level->level,
                    'name' => $level->name,
                    'icon' => $level->icon,
                    'frame_color' => $level->frame_color,
                    'required_tours' => $level->required_tours,
                    'required_reviews' => $level->required_reviews,
                    'required_blogs' => $level->required_blogs,
                    'discount_percent' => (float) $level->discount_percent,
                    'benefits' => $level->benefits,
                ];
            }),
            'Lấy danh sách level thành công'
        );
    }

    /**
     * Lấy thông tin level hiện tại và tiến trình của user
     */
    public function myLevel()
    {
        $user = Auth::user();
        $result = $this->levelService->calculateLevel($user);

        $currentLevel = $result['level'];
        $nextLevel = $result['next_level'];

        return $this->successResponse([
            'current_level' => $currentLevel ? [
                'level' => $currentLevel->level,
                'name' => $currentLevel->name,
                'icon' => $currentLevel->icon,
                'frame_color' => $currentLevel->frame_color,
                'discount_percent' => (float) $currentLevel->discount_percent,
                'benefits' => $currentLevel->benefits,
            ] : null,
            'stats' => $result['stats'],
            'next_level' => $nextLevel ? [
                'level' => $nextLevel->level,
                'name' => $nextLevel->name,
                'icon' => $nextLevel->icon,
                'frame_color' => $nextLevel->frame_color,
                'discount_percent' => (float) $nextLevel->discount_percent,
                'required_tours' => $nextLevel->required_tours,
                'required_reviews' => $nextLevel->required_reviews,
                'required_blogs' => $nextLevel->required_blogs,
            ] : null,
            'progress' => $result['progress'],
        ], 'Lấy thông tin level thành công');
    }
}
