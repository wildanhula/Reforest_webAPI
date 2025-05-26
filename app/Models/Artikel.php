<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Artikel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'artikel';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'isi',
        'author',
    ];

    /**
     * Relasi ke model User (penulis artikel)
     */
    public function penulis()
    {
        return $this->belongsTo(User::class, 'author');
    }
    public function images()
{
    return $this->hasMany(ArtikelImage::class, 'artikel_id');
}
}
