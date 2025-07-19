<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    // ✅ 결과 목록 조회
    public function index()
    {
        $user = auth()->user();

        $results = $user->trains()->latest()->paginate(6);

        return response()->json($results);
    }

    // ✅ 단일 결과 상세 조회
    public function show($id)
    {
        $user = auth()->user();

        $result = $user->trains()->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.'
            ], 404);
        }

        return response()->json($result);
    }

    // ✅ 결과 삭제
    public function destroy($id)
    {
        $user = auth()->user();
        $result = $user->trains()->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.'
            ], 404);
        }

        // 이미지 파일 삭제 (있는 경우)
        if ($result->url && Storage::disk('public')->exists('images/' . $result->url)) {
            try {
                Storage::disk('public')->delete('images/' . $result->url);
            } catch (\Exception $e) {
                Log::warning('이미지 삭제 실패', [
                    'file' => $result->url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result->delete();

        return response()->json([
            'message' => '삭제 완료'
        ], 200);
    }
}
