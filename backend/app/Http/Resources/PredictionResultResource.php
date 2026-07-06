<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredictionResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $predictionCache = $this->predictionCache;

        return [
            'id' => $this->id,
            'url' => $this->url,
            'originalName' => $this->original_name,
            'cropName' => $predictionCache?->crop_name,
            'sickNameKor' => $predictionCache?->sick_name,
            'confidence' => $predictionCache?->confidence,
            'userOpinion' => $this->user_opinion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
