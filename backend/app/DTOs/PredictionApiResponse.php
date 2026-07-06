<?php

namespace App\DTOs;

final readonly class PredictionApiResponse
{
    public function __construct(
        public string $cropNameKor,
        public string $sickNameKor,
        public float $confidence,
    ) {
    }
}
