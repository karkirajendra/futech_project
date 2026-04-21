<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
     use HasFactory, SoftDeletes;
    //
     protected $fillable = [
        'blog_id',
        'user_id',
        'content',
    ];

      protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
     public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
