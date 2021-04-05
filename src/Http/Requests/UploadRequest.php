<?php

namespace Kraenkvisuell\NovaCmsMedia\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadRequest extends FormRequest
{
	protected function failedValidation(Validator $validator) {
		throw new HttpResponseException(response()->json([
			'message' => $validator->errors()->first()
		], 422));
	}

	public function rules()
	{
		return [
			'file' => 'required|file'
		];
	}

	public function messages()
	{
		return [
			'file.*' => __('Invalid file')
		];
	}

}
