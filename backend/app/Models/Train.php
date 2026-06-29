<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'original_name',
        'user_opinion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function predictionCache()
    {
        return $this->belongsTo(PredictionCache::class);
    }
}

