<?php

namespace App\Http\Requests;

use App\Logging\CorrelationContext;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvailabilityQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'businessId' => 'required|integer|exists:businesses,id',
            'serviceId'  => 'required|integer',
            'date'       => 'nullable|date_format:Y-m-d|after_or_equal:today|before_or_equal:' . now()->addMonths(6)->toDateString(),
            'timezone'   => 'nullable|string|max:64|timezone',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'businessId' => $this->route('businessId') ?? $this->input('businessId'),
            'serviceId'  => $this->route('serviceId') ?? $this->input('serviceId'),
            'date'       => $this->route('date') ?? $this->input('date'),
            'timezone'   => $this->route('preferredTimezone') ?? $this->input('timezone'),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
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
}
