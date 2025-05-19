<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Train;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PredictController extends Controller
{
    public function index()
    {
        $photos = Train::latest()->paginate(5);
        return response()->json($photos);
    }

    public function store(Request $request)
    {
        if (!$request->hasFile('photo')) {
            return response()->json(['error' => '사진을 첨부해주세요'], 422);
        }

        $modelUrl = env('modelApi');
        if (Http::get($modelUrl)->serverError()) {
            return response()->json(['error' => '서버와의 연결이 끊어졌습니다.'], 503);
        }

        [$hashname, $existingTrain, $validatedData] = $this->validateDuplicatePhoto($request);

        if ($existingTrain) {
            $photo = $this->createDataFromExisting($existingTrain, $hashname);
        } else {
            try {
                [$cropName, $sickNameKor, $confidence, $path] = $this->fileUpload($modelUrl, $validatedData['photo'], $request);
                $photo = $this->storePhoto($path, $hashname, $request, $cropName, $sickNameKor, $confidence);
            } catch (\Exception $e) {
                return response()->json(['error' => 'AI 분석 오류: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'message' => '업로드 완료',
            'data' => $photo
        ], 201);
    }

    public function opinionStore(Request $request, $id)
    {
        $request->validate([
            'cropName' => 'required|string',
            'sickNameKor' => 'required|string',
        ]);

        $userOpinion = $request->cropName . '_' . $request->sickNameKor;

        $train = Train::findOrFail($id);
        $train->userOpinion = $userOpinion;
        $train->save();

        return response()->json(['message' => '의견이 반영되었습니다']);
    }

    protected function fileUpload($modelUrl, $photoFile, Request $request): array
    {
        $url = $modelUrl . '/predict';

        $response = Http::attach('image', file_get_contents($photoFile), $photoFile->getClientOriginalName())
            ->post($url);

        if ($response->failed()) {
            throw new \Exception("API 응답 오류");
        }

        $cropName = $response->json('cropName');
        $sickNameKor = $response->json('sickNameKor');
        $confidence = $response->json('confidence');

        $path = $photoFile->store("public/{$cropName}_{$sickNameKor}", 's3');

        return [$cropName, $sickNameKor, $confidence, $path];
    }

    protected function storePhoto($path, $hashname, Request $request, $cropName, $sickNameKor, $confidence)
    {
        return Train::create([
            'url' => Storage::disk('s3')->url($path),
            'hashname' => $hashname,
            'originalname' => $request->file('photo')->getClientOriginalName(),
            'cropName' => $cropName,
            'sickNameKor' => $sickNameKor,
            'confidence' => $confidence,
        ]);
    }

    protected function createDataFromExisting($existingTrain, $hashname)
    {
        return Train::create([
            'url' => $existingTrain->url,
            'hashname' => $hashname,
            'originalname' => $existingTrain->originalname,
            'cropName' => $existingTrain->cropName,
            'sickNameKor' => $existingTrain->sickNameKor,
            'confidence' => $existingTrain->confidence,
        ]);
    }

    protected function validateDuplicatePhoto(Request $request): array
    {
        $hashname = md5_file($request->file('photo')->getRealPath());

        $existingTrain = Train::where('hashname', $hashname)->latest()->first();

        $validatedData = $request->validate([
            'photo' => 'required|mimes:jpeg,bmp,png,jpg'
        ]);

        return [$hashname, $existingTrain, $validatedData];
    }
}
