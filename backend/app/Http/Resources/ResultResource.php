<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'originalName' => $this->original_name,
            'cropName' => $this->predictionCache?->crop_name,
            'sickNameKor' => $this->predictionCache?->sick_name,
            'confidence' => $this->predictionCache?->confidence,
            'userOpinion' => $this->user_opinion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
