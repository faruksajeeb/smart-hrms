<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CvExtractionService
{
    public function __construct(
        protected DocumentTextExtractor $textExtractor,
        protected OcrTextExtractor $ocrTextExtractor,
        protected TraditionalResumeParser $resumeParser,
    ) {}

    /** @return array<string, string> */
    public function extract(UploadedFile $file, int $userId): array
    {
        $disk = config('filesystems.document_analyses_disk', 'local');
        $path = $file->store('cv-uploads/'.$userId, $disk);

        try {
            try {
                $text = $this->textExtractor->extract($disk, $path, $file->getMimeType(), $file->getClientOriginalName());
            } catch (\Throwable) {
                $text = $this->ocrTextExtractor->extract(
                    Storage::disk($disk)->path($path),
                    strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)),
                );
            }

            if (mb_strlen(trim($text)) < 40) {
                try {
                    $ocrText = $this->ocrTextExtractor->extract(
                        Storage::disk($disk)->path($path),
                        strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)),
                    );
                    if (mb_strlen($ocrText) > mb_strlen($text)) {
                        $text = $ocrText;
                    }
                } catch (\Throwable) {
                    // Direct text extraction remains useful when OCR is unavailable.
                }
            }

            return $this->resumeParser->parse($text);
        } finally {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }
}
