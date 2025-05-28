<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class FaqModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'post_faq';

    protected $fillable = [
        'question',
        'answer',
        'user_id',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
