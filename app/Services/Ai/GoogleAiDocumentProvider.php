<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\DocumentAiProvider;
use Illuminate\Support\Facades\Http;

class GoogleAiDocumentProvider implements DocumentAiProvider
{
    public function key(): string
    {
        return 'google';
    }

    public function label(): string
    {
        return 'Google AI Studio';
    }

    public function isConfigured(): bool
    {
        $apiKey = config('services.google_ai.api_key');

        return is_string($apiKey) && $apiKey !== '';
    }

    public function analyze(string $documentText, string $prompt): string
    {
        $apiKey = config('services.google_ai.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new \RuntimeException('Google AI API key is not configured. Set GOOGLE_AI_API_KEY in your environment.');
        }

        $model = config('services.google_ai.model', 'gemini-2.0-flash');
        $baseUrl = rtrim((string) config('services.google_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        $httpResponse = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
        ])
            ->timeout((int) config('services.google_ai.timeout', 120))
            ->post("{$baseUrl}/models/{$model}:generateContent", [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $this->systemInstruction()],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $this->userPrompt($documentText, $prompt)],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                ],
            ]);

        if (! $httpResponse->successful()) {
            $message = $httpResponse->json('error.message')
                ?? $httpResponse->json('error.status')
                ?? $httpResponse->body();

            throw new \RuntimeException((string) $message);
        }

        $content = $httpResponse->json('candidates.0.content.parts.0.text');

        if (! is_string($content) || trim($content) === '') {
            throw new \RuntimeException('Google AI returned an empty response.');
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
