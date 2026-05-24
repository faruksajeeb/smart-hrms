<?php

namespace App\Jobs;

use App\Models\DocumentAnalysis;
use App\Services\DocumentAnalyzerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeDocumentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DocumentAnalysis $documentAnalysis,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DocumentAnalyzerService $analyzer): void
    {
        $analysis = $this->documentAnalysis->fresh();

        if ($analysis === null || $analysis->isFinished()) {
            return;
        }

        $analyzer->analyze($analysis);
    }
}
