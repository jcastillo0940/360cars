<?php

namespace App\Http\Requests\Auth;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'account_type' => ['required', 'in:'.implode(',', [
                AccountType::Buyer->value,
                AccountType::Seller->value,
                AccountType::Dealer->value,
            ])],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => ['nullable', 'string', 'max:30'],
            'agency_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:60'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
