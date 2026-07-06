<?php

namespace App\Exceptions\Prediction;

final class PredictionUpstreamException extends PredictionException
{
    public function __construct(
        public readonly int $status,
        public readonly ?string $errorCode,
        string $message
    ) {
        parent::__construct($message);
    }

    public function responseStatus(): int
    {
        return in_array($this->status, [400, 413, 415, 422], true)
            ? $this->status
            : 502;
    }
}
