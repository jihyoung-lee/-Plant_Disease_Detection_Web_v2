<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'hashname',
        'crop_name',
        'sick_name',
        'confidence',
    ];

    protected $casts = [
        'confidence' => 'float',
    ];

    public function trains()
    {
        return $this->hasMany(Train::class);
    }
}
