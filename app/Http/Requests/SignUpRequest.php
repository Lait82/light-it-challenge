<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "email"=> ["required","email", "string", "unique:users"],
            "name" => ["required", "string"],
            "password" => ["required", Password::min(8)->mixedCase()->numbers()],
            "address" => ["required", "string"],
            "phone_number" => ["required", "string"],
            "id_photo" => ["required", File::types(['jpg', 'png', 'jpeg'])
            ->min(256)
            ->max(12 * 1024)
            ]
        ];
    }
}
