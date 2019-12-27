<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class OrderCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin' => 'bail|required|array|size:2',
            'destination' => 'bail|required|array|size:2|different:origin',

            'origin.0' => [
                'required',
                'string',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'
            ],
            'origin.1' => [
                'required',
                'string',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'
            ],

            'destination.0' => [
                'required',
                'string',
                'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'
            ],
            'destination.1' => [
                'required',
                'string',
                'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'origin.0.required' => __('Origin Latitude is required'),
            'origin.1.required' => __('Origin Longitude is required'),
            'destination.0.required' => __('Destination Latitude is required'),
            'destination.1.required' => __('Destination Longitude is required'),

            'origin.0.string' => __('Origin Latitude must be string'),
            'origin.1.string' => __('Origin Longitude must be string'),
            'destination.0.string' => __('Destination Latitude must be string'),
            'destination.1.string' => __('Destination Longitude must be string'),

            'origin.0.regex' => __('Origin Latitude must be valid'),
            'origin.1.regex' => __('Origin Longitude must be valid'),
            'destination.0.regex' => __('Destination Latitude must be valid'),
            'destination.1.regex' => __('Destination Longitude must be valid'),
        ];

        return $messages;
    }

    /**
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => implode(', ', $validator->errors()->all())
        ], 422));
    }
}
