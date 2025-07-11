<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'hashname',
        'originalname',
        'cropName',
        'sickNameKor',
        'confidence',
        'userOpinion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

