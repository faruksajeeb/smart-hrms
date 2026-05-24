<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\DocumentAiProvider;
use Illuminate\Support\Facades\Http;

class OpenAiDocumentProvider implements DocumentAiProvider
{
    public function key(): string
    {
        return 'openai';
    }

    public function label(): string
    {
        return 'OpenAI';
    }

    public function isConfigured(): bool
    {
        $apiKey = config('services.openai.api_key');

        return is_string($apiKey) && $apiKey !== '';
    }

    public function analyze(string $documentText, string $prompt): string
    {
        $apiKey = config('services.openai.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new \RuntimeException('OpenAI API key is not configured. Set OPENAI_API_KEY in your environment.');
        }

        $model = config('services.openai.model', 'gpt-4o-mini');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        $httpResponse = Http::withToken($apiKey)
            ->timeout((int) config('services.openai.timeout', 120))
            ->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemInstruction(),
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->userPrompt($documentText, $prompt),
                    ],
                ],
                'temperature' => 0.2,
            ]);

        if (! $httpResponse->successful()) {
            $message = $httpResponse->json('error.message') ?? $httpResponse->body();

            throw new \RuntimeException((string) $message);
        }

        $content = $httpResponse->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('OpenAI returned an empty response.');
        }

        return trim($content);
    }

    protected function systemInstruction(): string
    {
        return 'You are an HR document analyst. Follow the user instructions precisely. Base your answer only on the provided document text. If the document does not contain enough information, say so clearly.';
    }

    protected function userPrompt(string $documentText, string $prompt): string
    {
        return "Document:\n\n{$documentText}\n\n---\n\nInstructions:\n{$prompt}";
    }
}
