<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Train;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\Translation\t;

class PredictController extends Controller
{
    public function index()
    {
        $photos = Train::latest()->paginate(5);
        return response()->json($photos);
    }

    public function store(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => '사진을 첨부해주세요'], 422);
        }

        $modelUrl = config('services.predict.endpoint');;

      /*  if (Http::post($modelUrl)->serverError()) {
            return response()->json(['error' => '서버와의 연결이 끊어졌습니다.'], 503);
        }*/
        [$hashname, $existingTrain, $validatedData] = $this->validateDuplicatePhoto($request);


        if ($existingTrain) {
            // 중복이면 insert 하지 않고 기존 데이터만 반환
            return response()->json([
                'message' => '중복된 이미지입니다. 기존 데이터를 반환합니다.',
                'data' => $existingTrain
            ], 200);
        } else {
            try {
                [$cropName, $sickNameKor, $confidence, $path] = $this->fileUpload($modelUrl, $validatedData['image'], $request);
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

        try {
            $train = Train::findOrFail($id);
            $train->userOpinion = $userOpinion;
            $train->save();

            return response()->json(['message' => '의견이 반영되었습니다'], 201);
        } catch (\Exception $e) {
            Log::error('의견 전송 실패: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => '의견 전송에 실패했습니다. 잠시 후 다시 시도해주세요.'
            ], 500);
        }

    }

    protected function fileUpload(string $modelUrl, $photoFile, Request $request): array
    {
        $inputCropName = $request->input('cropName');

        try {
            // FastAPI로 이미지와 cropName 함께 전송
            $response = Http::attach(
                'image',
                file_get_contents($photoFile),
                $photoFile->getClientOriginalName()
            )->post($modelUrl, [
                'cropName' => $inputCropName
            ]);

            if ($response->failed()) {
                throw new \Exception("AI 분석 서버로부터 실패 응답을 받았습니다.");
            }

            // FastAPI 응답 파싱
            $predictedCropName = $response->json('cropName') ?? '알수없음';
            $sickNameKor = $response->json('sickNameKor') ?? '알수없음';
            $confidence = $response->json('confidence') ?? 0;

            // 고유한 해시 파일명 생성 및 저장
            $hashname = $photoFile->hashName();
            $path = $photoFile->storeAs('images', $hashname, 'public');

            return [$predictedCropName, $sickNameKor, $confidence, $path];

        } catch (\Exception $e) {
            throw new \Exception("fileUpload 실패: " . $e->getMessage(), 500);
        }
    }


    protected function storePhoto($path, $hashname, Request $request, $cropName, $sickNameKor, $confidence)
    {
            return Train::create([
                #'url' => Storage::disk('s3')->url($path),
                'url' => Storage::disk('public')->url($path), // public/storage/images/xxx.jpg
                'hashname' => $hashname,
                'originalname' => $request->file('image')->getClientOriginalName(),
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
        $validatedData = $request->validate([
            'image' => 'required|mimes:jpeg,bmp,png,jpg'
        ]);

        $hashname = md5_file($validatedData['image']->getRealPath());

        $existingTrain = Train::where('hashname', $hashname)->latest()->first();



        return [$hashname, $existingTrain, $validatedData];
    }
}
