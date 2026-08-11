<?php

namespace App\Http\Requests;

use App\TG\Security\UrlGuard;
use App\Exceptions\SsrfPolicyException;
use Illuminate\Foundation\Http\FormRequest;

class HumanresourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255'],
            'calendar_link' => ['nullable', 'url:https', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $calendarLink = $this->input('calendar_link');

            if (empty($calendarLink)) {
                return;
            }

            try {
                $guard = new UrlGuard();
                $guard->validateUrl($calendarLink);
            } catch (SsrfPolicyException $e) {
                $validator->errors()->add(
                    'calendar_link',
                    $e->getOwnerFacingMessage()
                );
            }
        });
    }
}
