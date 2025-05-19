<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Train;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PredictController extends Controller
{
    public function show()
    {
        $photos = Train::latest()->paginate(5);
        return view('list.dashboard', ['Photos' => $photos]);
    }

    public function store(Request $request)
    {
        if (!$request->hasFile('photo')) {
            return redirect()->back()->withErrors(['error' => '사진을 첨부해주세요']);
        }

        $modelUrl = env('modelApi');
        if (Http::get($modelUrl)->serverError()) {
            return redirect()->back()->withErrors(['error' => '서버와의 연결이 끊어졌습니다. 잠시 후에 다시 시도해주세요']);
        }

        [$hashname, $existingTrain, $validatedData] = $this->validateDuplicatePhoto($request);

        if ($existingTrain) {
            $photo = $this->createDataFromExisting($existingTrain, $hashname);
        } else {
            try {
                [$cropName, $sickNameKor, $confidence, $path] = $this->fileUpload($modelUrl, $validatedData['photo'], $request);
                $photo = $this->storePhoto($path, $hashname, $request, $cropName, $sickNameKor, $confidence);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => 'AI 분석 중 오류 발생: ' . $e->getMessage()]);
            }
        }

        return redirect()->back()->with([
            'id' => $photo->id,
            'status' => '정상적으로 업로드 되었습니다.'
        ]);
    }
    public function fileUpload($modelUrl, $photoFile, Request $request): array
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

        $path = $request->file('photo')->store("public/{$cropName}_{$sickNameKor}", 's3');

        return [$cropName, $sickNameKor, $confidence, $path];
    }
    public function storePhoto($path, $hashname, Request $request, $cropName, $sickNameKor, $confidence)
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
    public function createDataFromExisting($existingTrain, $hashname)
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
    public function opinionStore(Request $request, $id)
    {
        $cropName = $request->input('cropName');
        $sickNameKor = $request->input('sickNameKor');
        $userOpinion = $cropName . '_' . $sickNameKor;

        Train::where('id', $id)->update(['userOpinion' => $userOpinion]);

        return redirect()->back()->with([
            'status' => '보내주신 의견은 검토 후 모델 재학습에 반영됩니다'
        ]);
    }


    public function validateDuplicatePhoto(Request $request): array
    {
        $hashname = md5_file($request->file('photo')->getRealPath());

        $existingTrain = Train::where('hashname', $hashname)->latest()->first();

        $validatedData = $request->validate([
            'photo' => 'required|mimes:jpeg,bmp,png,jpg'
        ]);

        return [$hashname, $existingTrain, $validatedData];
    }

}
