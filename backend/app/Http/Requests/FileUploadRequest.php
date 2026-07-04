<?php

namespace App\Http\Requests;

use App\Enums\CropName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
            'cropName' => ['required', 'string', 'max:50', Rule::in(CropName::values())],
        ];
    }
}
