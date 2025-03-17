<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'access_tier',
        'views',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'content_views')
            ->withTimestamps();
    }
} 