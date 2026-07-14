<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class OcrTextExtractor
{
    public function extract(string $path, string $extension): string
    {
        if ($extension !== 'pdf') {
            return trim($this->runProcess(['tesseract', $path, 'stdout', '-l', 'eng'], 'OCR'));
        }

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'smart-hr-ocr-'.uniqid();
        if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Unable to create a temporary OCR directory.');
        }

        $prefix = $directory.DIRECTORY_SEPARATOR.'page';

        try {
            $this->runProcess(['pdftoppm', '-png', '-r', '200', $path, $prefix], 'PDF OCR conversion');
            $pages = glob($prefix.'-*.png') ?: [];
            if ($pages === []) {
                throw new \RuntimeException('No pages were produced for OCR.');
            }

            return trim(implode("\n\n", array_map(
                fn (string $page) => $this->runProcess(['tesseract', $page, 'stdout', '-l', 'eng'], 'OCR'),
                $pages,
            )));
        } finally {
            foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $temporaryFile) {
                @unlink($temporaryFile);
            }
            @rmdir($directory);
        }
    }

    /** @param array<int, string> $command */
    protected function runProcess(array $command, string $operation): string
    {
        $process = new Process($command);
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput());
            throw new \RuntimeException($error !== '' ? "{$operation} failed: {$error}" : "{$operation} requires Tesseract and Poppler to be installed.");
        }

        return $process->getOutput();
    }
}
