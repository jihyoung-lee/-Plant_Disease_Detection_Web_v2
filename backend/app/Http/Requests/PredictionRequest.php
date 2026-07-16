<?php

namespace App\Http\Requests;

use App\Enums\CropName;
use Illuminate\Validation\Rule;

class PredictionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
            'cropName' => ['required', 'string', 'max:50', Rule::in(CropName::codes())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $cropCode = $this->input('cropName');

        if (is_string($cropCode)) {
            $this->merge([
                'cropName' => strtolower(trim($cropCode)),
            ]);
        }
    }
}
