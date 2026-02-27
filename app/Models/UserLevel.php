<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'name',
        'icon',
        'frame_color',
        'required_tours',
        'required_reviews',
        'required_blogs',
        'discount_percent',
        'benefits',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'discount_percent' => 'decimal:2',
            'required_tours' => 'integer',
            'required_reviews' => 'integer',
            'required_blogs' => 'integer',
            'level' => 'integer',
        ];
    }

    /**
     * Lấy level config theo level number
     */
    public static function getForLevel(int $level): ?self
    {
        return static::where('level', $level)->first();
    }

    /**
     * Lấy tất cả levels sắp xếp tăng dần
     */
    public static function getAllLevels()
    {
        return static::orderBy('level', 'asc')->get();
    }
}
