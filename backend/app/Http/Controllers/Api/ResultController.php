<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $results = $request->user()
            ->trains()
            ->with('predictionCache')
            ->latest()
            ->paginate(6);

        $results->getCollection()->transform(
            fn (Train $train) => $this->serializeTrain($train)
        );

        return response()->json($results);
    }

    public function show(Request $request, $id)
    {
        $result = $request->user()
            ->trains()
            ->with('predictionCache')
            ->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        return response()->json($this->serializeTrain($result));
    }

    public function destroy(Request $request, $id)
    {
        $result = $request->user()->trains()->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        $storagePath = $this->storagePathFromUrl($result->url);

        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            try {
                Storage::disk('public')->delete($storagePath);
            } catch (Throwable $e) {
                Log::warning('이미지 삭제 실패', [
                    'file' => $storagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result->delete();

        return response()->json(['message' => '삭제 완료']);
    }

    protected function storagePathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        $marker = '/storage/';
        $position = strpos($path, $marker);

        if ($position === false) {
            return null;
        }

        return ltrim(substr($path, $position + strlen($marker)), '/');
    }

    protected function serializeTrain(Train $train): array
    {
        $cachedPrediction = $train->predictionCache;

        return [
            'id' => $train->id,
            'url' => $train->url,
            'hashname' => $cachedPrediction?->hashname,
            'originalName' => $train->original_name,
            'cropName' => $cachedPrediction?->crop_name,
            'sickNameKor' => $cachedPrediction?->sick_name,
            'confidence' => $cachedPrediction?->confidence,
            'userOpinion' => $train->user_opinion,
            'created_at' => $train->created_at,
            'updated_at' => $train->updated_at,
        ];
    }
}
