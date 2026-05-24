<?php

namespace App\Http\Requests\Admin;

use App\Models\DocumentAnalysis;
use App\Services\Ai\DocumentAiProviderResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentAnalysisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('doc-analyzer.view-doc-analyzer') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,txt,md,csv,docx',
            ],
            'prompt' => ['required', 'string', 'min:10', 'max:5000'],
            'ai_provider' => [
                'required',
                'string',
                Rule::in(DocumentAnalysis::providers()),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $provider = (string) $this->input('ai_provider');
            $resolver = app(DocumentAiProviderResolver::class);

            if (! $resolver->isConfigured($provider)) {
                $validator->errors()->add(
                    'ai_provider',
                    $resolver->labelFor($provider).' is not configured. Add the API key to your environment file.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.required' => 'Please upload a document to analyze.',
            'document.mimes' => 'Supported formats: PDF, DOCX, TXT, MD, and CSV.',
            'prompt.min' => 'Please provide at least 10 characters of instructions.',
            'ai_provider.required' => 'Please choose an AI provider.',
            'ai_provider.in' => 'The selected AI provider is not supported.',
        ];
    }
}
