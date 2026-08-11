<?php

namespace App\Http\Requests;

use App\Logging\CorrelationContext;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'businessId'  => 'required|integer|exists:businesses,id',
            'service_id'  => 'required|integer',
            '_date'       => 'required|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . now()->addMonths(6)->toDateString(),
            '_time'       => 'required|date_format:H:i:s',
            '_timezone'   => 'nullable|string|max:64|timezone',
            'contact_id'  => 'nullable|integer',
            'email'       => 'nullable|email|max:255',
            'comments'    => 'nullable|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'error' => [
                        'code'           => 'validation_failed',
                        'message'        => 'The given data was invalid.',
                        'correlation_id' => CorrelationContext::id(),
                        'fields'         => $validator->errors()->toArray(),
                    ],
                ], 400)
            );
        }

        parent::failedValidation($validator);
    }
}
