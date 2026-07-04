<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '입력값이 올바르지 않습니다.',
                    'details' => $validator->errors(),
                ],
            ], 422)
        );
    }
}
