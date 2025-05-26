<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Artikel;

class ArtikelImage extends Model
{
    
    protected $table = 'artikel_images';

    protected $fillable = [
        'artikel_id',
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

    public function artikel()
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }
      public function images()
    {
        return $this->hasMany(ArtikelImage::class, 'artikel_id');
    }
}
