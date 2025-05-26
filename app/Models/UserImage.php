<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'mime_type',
        'file_size'
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return url('storage/images/' . $this->filename);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
