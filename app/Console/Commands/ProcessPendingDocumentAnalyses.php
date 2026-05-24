<?php

namespace App\Console\Commands;

use App\Models\DocumentAnalysis;
use App\Services\DocumentAnalyzerService;
use Illuminate\Console\Command;

class ProcessPendingDocumentAnalyses extends Command
{
    /**
     * @var string
     */
    protected $signature = 'document-analyzer:process-pending';

    /**
     * @var string
     */
    protected $description = 'Run analysis for document records still marked as pending';

    public function handle(DocumentAnalyzerService $analyzer): int
    {
        $pending = DocumentAnalysis::query()
            ->where('status', DocumentAnalysis::STATUS_PENDING)
            ->orderBy('id')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending document analyses found.');

            return self::SUCCESS;
        }

        foreach ($pending as $analysis) {
            $this->line("Processing #{$analysis->id}: {$analysis->original_filename}");
            $analyzer->analyze($analysis->fresh());
            $analysis->refresh();
            $this->line("  → {$analysis->status}");
        }

        return self::SUCCESS;
    }
}
