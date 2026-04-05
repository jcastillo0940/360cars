<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_slug' => ['required', 'string', Rule::exists('plans', 'slug')->where('is_active', true)],
            'provider' => ['nullable', Rule::in(['sandbox', 'paypal', 'tilopay', 'sinpe_movil'])],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'auto_renews' => ['nullable', 'boolean'],
            'activate_now' => ['nullable', 'boolean'],
        ];
    }
}
