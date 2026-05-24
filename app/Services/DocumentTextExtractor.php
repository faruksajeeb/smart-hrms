<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentTextExtractor
{
    /**
     * Extract plain text from a stored document.
     */
    public function extract(string $disk, string $path, ?string $mimeType, string $originalFilename): string
    {
        $absolutePath = Storage::disk($disk)->path($path);
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        $text = match (true) {
            $extension === 'pdf' || $mimeType === 'application/pdf' => $this->extractPdf($absolutePath),
            in_array($extension, ['docx'], true) || $mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocx($absolutePath),
            in_array($extension, ['txt', 'md', 'csv', 'log'], true) => $this->extractPlainText($absolutePath),
            default => throw new \RuntimeException('Unsupported document type. Upload PDF, DOCX, TXT, MD, or CSV.'),
        };

        $text = trim(preg_replace("/\n{3,}/u", "\n\n", $text) ?? '');

        if ($text === '') {
            throw new \RuntimeException('No readable text could be extracted from this document.');
        }

        return $text;
    }

    protected function extractPlainText(string $path): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read the uploaded file.');
        }

        return $contents;
    }

    protected function extractPdf(string $path): string
    {
        $parser = new PdfParser;

        return $parser->parseFile($path)->getText();
    }

    protected function extractDocx(string $path): string
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new \RuntimeException(
                'DOCX support requires the PHP zip extension. In Laragon: Menu → PHP → php.ini → uncomment extension=zip, then restart Apache/PHP and your queue worker.',
            );
        }

        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to read the DOCX file.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new \RuntimeException('Invalid DOCX structure.');
        }

        $xml = str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml);

        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
