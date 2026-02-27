<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Services\UserLevelService;

class BlogPostObserver
{
    /**
     * Handle the BlogPost "updated" event.
     * Khi blog post được publish → tính lại level user
     */
    public function updated(BlogPost $blogPost): void
    {
        if ($blogPost->isDirty('status') && $blogPost->status === 'published') {
            $levelService = new UserLevelService();
            $levelService->updateUserLevel($blogPost->user);
        }
    }

    /**
     * Handle the BlogPost "created" event.
     * Khi blog post mới tạo với status published → tính lại level
     */
    public function created(BlogPost $blogPost): void
    {
        if ($blogPost->status === 'published') {
            $levelService = new UserLevelService();
            $levelService->updateUserLevel($blogPost->user);
        }
    }
}
