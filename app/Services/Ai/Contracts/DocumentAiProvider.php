<?php

namespace App\Services\Ai\Contracts;

interface DocumentAiProvider
{
    public function key(): string;

    public function label(): string;

    public function isConfigured(): bool;

    /**
     * @throws \RuntimeException
     */
    public function analyze(string $documentText, string $prompt): string;
}
