<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    /**
     * Anyone (including guests) can view lists of blogs.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone (including guests) can view a single blog.
     */
    public function view(?User $user, Blog $blog): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create blogs.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the blog owner can update the blog.
     */
    public function update(User $user, Blog $blog): bool
    {
        return $user->id === $blog->user_id;
    }

    /**
     * Only the blog owner can delete the blog.
     */
    public function delete(User $user, Blog $blog): bool
    {
        return $user->id === $blog->user_id;
    }

    public function restore(User $user, Blog $blog): bool
    {
        return $user->id === $blog->user_id;
    }

    public function forceDelete(User $user, Blog $blog): bool
    {
        return $user->id === $blog->user_id;
    }
}
