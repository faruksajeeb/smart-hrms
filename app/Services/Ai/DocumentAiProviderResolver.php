<?php

namespace App\Services\Ai;

use App\Models\DocumentAnalysis;
use App\Services\Ai\Contracts\DocumentAiProvider;
use InvalidArgumentException;

class DocumentAiProviderResolver
{
    /**
     * @return list<DocumentAiProvider>
     */
    public function all(): array
    {
        return [
            app(OpenAiDocumentProvider::class),
            app(GoogleAiDocumentProvider::class),
        ];
    }

    public function resolve(string $provider): DocumentAiProvider
    {
        $resolved = collect($this->all())->first(fn (DocumentAiProvider $candidate) => $candidate->key() === $provider);

        if ($resolved === null) {
            throw new InvalidArgumentException("Unsupported AI provider [{$provider}].");
        }

        return $resolved;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function configuredOptions(): array
    {
        return collect($this->all())
            ->filter(fn (DocumentAiProvider $provider) => $provider->isConfigured())
            ->map(fn (DocumentAiProvider $provider) => [
                'value' => $provider->key(),
                'label' => $provider->label(),
            ])
            ->values()
            ->all();
    }

    public function defaultProviderKey(): ?string
    {
        $configured = collect($this->configuredOptions());

        if ($configured->isEmpty()) {
            return null;
        }

        $preferred = (string) config('document-analyzer.default_provider', DocumentAnalysis::PROVIDER_OPENAI);

        if ($configured->contains('value', $preferred)) {
            return $preferred;
        }

        return $configured->first()['value'];
    }

    public function isConfigured(string $provider): bool
    {
        return $this->resolve($provider)->isConfigured();
    }

    public function labelFor(string $provider): string
    {
        return $this->resolve($provider)->label();
    }
}
