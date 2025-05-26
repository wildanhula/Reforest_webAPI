<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pohonku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pohonku';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'namaPohon',
        'jenis_pohon',
        'tanggal_tanam',
        'lat',
        'long',
        'user_id',
    ];

    /**
     * Relasi ke model User (pemilik pohon)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
