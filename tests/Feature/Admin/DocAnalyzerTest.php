<?php

use App\Models\DocumentAnalysis;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('admins with permission can view the document analyzer', function () {
    config([
        'services.openai.api_key' => 'test-openai-key',
        'services.google_ai.api_key' => null,
    ]);

    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $this->actingAs($admin)->get(route('admin.doc-analyzer.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/DocAnalyzer/Index')
            ->where('providers', fn ($providers) => collect($providers)->contains('value', DocumentAnalysis::PROVIDER_OPENAI))
        );
});

test('admins can upload a document for openai analysis', function () {
    Storage::fake('local');

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Employees must provide 30 days notice before resignation.',
                    ],
                ],
            ],
        ], 200),
    ]);

    config([
        'services.openai.api_key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
        'document-analyzer.queue' => false,
    ]);

    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $file = UploadedFile::fake()->createWithContent(
        'policy.txt',
        'Employee must provide 30 days notice before resignation.',
    );

    $response = $this->actingAs($admin)->post(route('admin.doc-analyzer.store'), [
        'document' => $file,
        'prompt' => 'Summarize the notice period requirement from this document.',
        'ai_provider' => DocumentAnalysis::PROVIDER_OPENAI,
    ]);

    $analysis = DocumentAnalysis::first();

    expect($analysis)->not->toBeNull();
    expect($analysis->user_id)->toBe($admin->id);
    expect($analysis->ai_provider)->toBe(DocumentAnalysis::PROVIDER_OPENAI);
    expect($analysis->status)->toBe(DocumentAnalysis::STATUS_COMPLETED);

    Storage::disk('local')->assertExists($analysis->file_path);

    $response->assertRedirect(route('admin.doc-analyzer.show', $analysis, absolute: false));
});

test('admins can upload a document for google ai analysis', function () {
    Storage::fake('local');

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => 'The policy grants 20 days of annual leave.'],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    config([
        'services.google_ai.api_key' => 'test-google-key',
        'services.google_ai.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        'services.google_ai.model' => 'gemini-2.0-flash',
        'document-analyzer.queue' => false,
    ]);

    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $file = UploadedFile::fake()->createWithContent(
        'policy.txt',
        'Annual leave entitlement is 20 days.',
    );

    $this->actingAs($admin)->post(route('admin.doc-analyzer.store'), [
        'document' => $file,
        'prompt' => 'Summarize leave entitlement from this document.',
        'ai_provider' => DocumentAnalysis::PROVIDER_GOOGLE,
    ])->assertRedirect();

    $analysis = DocumentAnalysis::first();

    expect($analysis->ai_provider)->toBe(DocumentAnalysis::PROVIDER_GOOGLE);
    expect($analysis->status)->toBe(DocumentAnalysis::STATUS_COMPLETED);
    expect($analysis->response)->toContain('20 days');
});

test('document analysis can be queued when enabled', function () {
    Queue::fake();
    Storage::fake('local');

    config([
        'document-analyzer.queue' => true,
        'services.openai.api_key' => 'test-key',
    ]);

    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $file = UploadedFile::fake()->createWithContent(
        'policy.txt',
        'Employee must provide 30 days notice before resignation.',
    );

    $this->actingAs($admin)->post(route('admin.doc-analyzer.store'), [
        'document' => $file,
        'prompt' => 'Summarize the notice period requirement from this document.',
        'ai_provider' => DocumentAnalysis::PROVIDER_OPENAI,
    ]);

    Queue::assertPushed(\App\Jobs\AnalyzeDocumentJob::class);
});

test('document analyzer service stores openai responses', function () {
    Storage::fake('local');

    $path = 'document-analyses/1/sample.txt';
    Storage::disk('local')->put($path, 'Annual leave entitlement is 20 days.');

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'The document states 20 days of annual leave.',
                    ],
                ],
            ],
        ], 200),
    ]);

    config([
        'services.openai.api_key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
    ]);

    $admin = User::factory()->create();
    $analysis = DocumentAnalysis::create([
        'user_id' => $admin->id,
        'original_filename' => 'sample.txt',
        'file_path' => $path,
        'mime_type' => 'text/plain',
        'file_size' => 42,
        'prompt' => 'How many annual leave days are mentioned?',
        'ai_provider' => DocumentAnalysis::PROVIDER_OPENAI,
        'status' => DocumentAnalysis::STATUS_PENDING,
    ]);

    app(\App\Services\DocumentAnalyzerService::class)->analyze($analysis->fresh());

    $analysis->refresh();

    expect($analysis->status)->toBe(DocumentAnalysis::STATUS_COMPLETED);
    expect($analysis->response)->toContain('20 days');
});

test('failed analyses are not sent to the ai provider again', function () {
    Storage::fake('local');

    Http::fake();

    config([
        'services.openai.api_key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
    ]);

    $admin = User::factory()->create();
    $analysis = DocumentAnalysis::create([
        'user_id' => $admin->id,
        'original_filename' => 'sample.txt',
        'file_path' => 'document-analyses/1/sample.txt',
        'mime_type' => 'text/plain',
        'file_size' => 42,
        'prompt' => 'Summarize',
        'ai_provider' => DocumentAnalysis::PROVIDER_OPENAI,
        'status' => DocumentAnalysis::STATUS_FAILED,
        'error_message' => 'OpenAI quota exceeded. Add billing or credits in your OpenAI account, then upload again.',
        'completed_at' => now(),
    ]);

    app(\App\Services\DocumentAnalyzerService::class)->analyze($analysis->fresh());

    Http::assertNothingSent();
    expect($analysis->fresh()->error_message)->toContain('quota exceeded');
});

test('openai quota errors are stored with a short user friendly message', function () {
    Storage::fake('local');

    $path = 'document-analyses/1/sample.txt';
    Storage::disk('local')->put($path, 'Sample text.');

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'You exceeded your current quota, please check your plan and billing details.',
            ],
        ], 429),
    ]);

    config([
        'services.openai.api_key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
    ]);

    $admin = User::factory()->create();
    $analysis = DocumentAnalysis::create([
        'user_id' => $admin->id,
        'original_filename' => 'sample.txt',
        'file_path' => $path,
        'mime_type' => 'text/plain',
        'file_size' => 42,
        'prompt' => 'Summarize',
        'ai_provider' => DocumentAnalysis::PROVIDER_OPENAI,
        'status' => DocumentAnalysis::STATUS_PENDING,
    ]);

    app(\App\Services\DocumentAnalyzerService::class)->analyze($analysis->fresh());

    $analysis->refresh();

    expect($analysis->status)->toBe(DocumentAnalysis::STATUS_FAILED);
    expect($analysis->error_message)->toBe(
        'OpenAI quota exceeded. Add billing or credits in your OpenAI account, then upload again.',
    );
});
