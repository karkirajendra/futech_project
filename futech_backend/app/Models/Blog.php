<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
class Blog extends Model
{
use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
    ];

protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    //  Get the full URL for the blog image.
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }

    // Delete blog image from storage.

    public function deleteImage(): void
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }
//Boot method to handle model events.
     protected static function boot()
    {
        parent::boot();

        // Delete image when blog is force deleted
        static::deleting(function ($blog) {
            if ($blog->isForceDeleting()) {
                $blog->deleteImage();
            }
        });
    }
}

