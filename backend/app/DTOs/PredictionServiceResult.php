<?php

namespace App\DTOs;

use App\Models\Train;

final readonly class PredictionServiceResult
{
    public function __construct(
        public Train $predictionRecord,
        public bool $cacheHit,
    ) {
    }
}
