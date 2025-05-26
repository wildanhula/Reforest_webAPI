<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostFaq extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'post_faq';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'question',
        'answer',
        'user_id',
    ];

    /**
     * Relasi ke model User (yang mengirim pertanyaan)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
