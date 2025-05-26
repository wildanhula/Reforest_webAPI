<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Artikel;

class PohonImage extends Model
{
    protected $table = 'pohon_images';

    protected $fillable = [
        'pohon_id',
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

    public function pohonku()
    {
        return $this->belongsTo(Pohonku::class, 'pohon_id');
    }
}
