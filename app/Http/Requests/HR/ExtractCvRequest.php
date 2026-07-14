<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class ExtractCvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage employees') ?? false;
    }

    public function rules(): array
    {
        return [
            'cv' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,txt,md,jpg,jpeg,png'],
        ];
    }
}
