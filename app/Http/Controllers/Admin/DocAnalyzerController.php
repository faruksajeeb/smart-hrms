<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentAnalysisRequest;
use App\Jobs\AnalyzeDocumentJob;
use App\Models\DocumentAnalysis;
use App\Services\Ai\DocumentAiProviderResolver;
use App\Services\DocumentAnalyzerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocAnalyzerController extends Controller
{
    public function __construct(
        protected DocumentAnalyzerService $documentAnalyzer,
        protected DocumentAiProviderResolver $providerResolver,
    ) {}

    /**
     * Display the document analyzer workspace.
     */
    public function index(Request $request): Response
    {
        $analyses = DocumentAnalysis::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (DocumentAnalysis $analysis) => $this->transformAnalysis($analysis));

        $providers = $this->providerResolver->configuredOptions();

        return Inertia::render('Admin/DocAnalyzer/Index', [
            'analyses' => $analyses,
            'providers' => $providers,
            'defaultProvider' => $this->providerResolver->defaultProviderKey(),
        ]);
    }

    /**
     * Store a new document analysis request.
     */
    public function store(StoreDocumentAnalysisRequest $request): RedirectResponse
    {
        
        $disk = config('filesystems.document_analyses_disk', 'local');
        $uploadedFile = $request->file('document');

        $path = $uploadedFile->store(
            'document-analyses/'.$request->user()->id,
            $disk,
        );

        $analysis = DocumentAnalysis::create([
            'user_id' => $request->user()->id,
            'original_filename' => $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize() ?: 0,
            'prompt' => $request->string('prompt')->toString(),
            'ai_provider' => $request->string('ai_provider')->toString(),
            'status' => DocumentAnalysis::STATUS_PENDING,
        ]);

        if (config('document-analyzer.queue')) {
            AnalyzeDocumentJob::dispatch($analysis);

            return to_route('admin.doc-analyzer.show', $analysis)
                ->with('success', 'Document uploaded. Analysis is in progress.');
        }

        $this->documentAnalyzer->analyze($analysis->fresh());
        $analysis->refresh();

        if ($analysis->status === DocumentAnalysis::STATUS_FAILED) {
            return to_route('admin.doc-analyzer.show', $analysis)
                ->with('error', $analysis->error_message ?? 'Document analysis failed.');
        }

        return to_route('admin.doc-analyzer.show', $analysis)
            ->with('success', 'Document analyzed successfully.');
    }

    /**
     * Display a single analysis result.
     */
    public function show(Request $request, DocumentAnalysis $documentAnalysis): Response
    {
        abort_unless($documentAnalysis->user_id === $request->user()->id, 403);

        return Inertia::render('Admin/DocAnalyzer/Show', [
            'analysis' => $this->transformAnalysis($documentAnalysis),
        ]);
    }

    /**
     * Remove an analysis and its stored file.
     */
    public function destroy(Request $request, DocumentAnalysis $documentAnalysis): RedirectResponse
    {
        abort_unless($documentAnalysis->user_id === $request->user()->id, 403);

        $this->documentAnalyzer->deleteFile($documentAnalysis);
        $documentAnalysis->delete();

        return to_route('admin.doc-analyzer.index')
            ->with('success', 'Analysis record deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformAnalysis(DocumentAnalysis $analysis): array
    {
        return [
            'id' => $analysis->id,
            'original_filename' => $analysis->original_filename,
            'file_size' => $analysis->file_size,
            'mime_type' => $analysis->mime_type,
            'prompt' => $analysis->prompt,
            'ai_provider' => $analysis->ai_provider ?? DocumentAnalysis::PROVIDER_OPENAI,
            'ai_provider_label' => $this->providerResolver->labelFor(
                $analysis->ai_provider ?? DocumentAnalysis::PROVIDER_OPENAI,
            ),
            'response' => $analysis->response,
            'status' => $analysis->status,
            'error_message' => $analysis->error_message,
            'is_finished' => $analysis->isFinished(),
            'created_at' => $analysis->created_at?->toIso8601String(),
            'completed_at' => $analysis->completed_at?->toIso8601String(),
        ];
    }
}
