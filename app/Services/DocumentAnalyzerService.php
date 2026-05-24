<?php

namespace App\Services;

use App\Models\DocumentAnalysis;
use App\Services\Ai\DocumentAiProviderResolver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentAnalyzerService
{
    public function __construct(
        protected DocumentTextExtractor $textExtractor,
        protected DocumentAiProviderResolver $providerResolver,
    ) {}

    /**
     * Run analysis for a stored document record.
     */
    public function analyze(DocumentAnalysis $analysis): void
    {
        $analysis = $analysis->fresh();

        if ($analysis === null || $analysis->isFinished()) {
            return;
        }

        $provider = $this->providerResolver->resolve($analysis->ai_provider);

        if (! $provider->isConfigured()) {
            $this->markFailed(
                $analysis,
                "{$provider->label()} API key is not configured.",
                $analysis->ai_provider,
            );

            return;
        }

        $analysis->update([
            'status' => DocumentAnalysis::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $disk = config('filesystems.document_analyses_disk', 'local');
            $text = $this->textExtractor->extract(
                $disk,
                $analysis->file_path,
                $analysis->mime_type,
                $analysis->original_filename,
            );

            $maxChars = (int) config('document-analyzer.max_document_chars', 120000);

            if (mb_strlen($text) > $maxChars) {
                $text = mb_substr($text, 0, $maxChars).'… [truncated]';
            }

            $response = $provider->analyze($text, $analysis->prompt);

            $analysis->update([
                'status' => DocumentAnalysis::STATUS_COMPLETED,
                'response' => $response,
                'error_message' => null,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            $this->markFailed($analysis, $exception->getMessage(), $analysis->ai_provider);
        }
    }

    protected function markFailed(DocumentAnalysis $analysis, string $message, string $provider): void
    {
        $analysis->update([
            'status' => DocumentAnalysis::STATUS_FAILED,
            'error_message' => $this->normalizeErrorMessage($message, $provider),
            'completed_at' => now(),
        ]);
    }

    protected function normalizeErrorMessage(string $message, string $provider): string
    {
        $message = trim(preg_replace('/^AI analysis failed:\s*/i', '', $message) ?? $message);

        $lower = strtolower($message);

        if (
            str_contains($lower, 'quota')
            || str_contains($lower, 'billing')
            || str_contains($lower, 'insufficient')
            || str_contains($lower, 'resource_exhausted')
        ) {
            return match ($provider) {
                DocumentAnalysis::PROVIDER_GOOGLE => 'Google AI quota exceeded. Check billing and limits in Google AI Studio, then upload again.',
                default => 'OpenAI quota exceeded. Add billing or credits in your OpenAI account, then upload again.',
            };
        }

        return mb_strlen($message) > 320 ? mb_substr($message, 0, 317).'…' : $message;
    }

    /**
     * Delete the stored file for an analysis record.
     */
    public function deleteFile(DocumentAnalysis $analysis): void
    {
        $disk = config('filesystems.document_analyses_disk', 'local');

        if ($analysis->file_path !== '' && Storage::disk($disk)->exists($analysis->file_path)) {
            Storage::disk($disk)->delete($analysis->file_path);
        }
    }
}
