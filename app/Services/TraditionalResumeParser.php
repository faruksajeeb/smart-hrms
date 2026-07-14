<?php

namespace App\Services;

use Carbon\Carbon;

class TraditionalResumeParser
{
    /** @return array<string, string> */
    public function parse(string $text): array
    {
        $text = $this->clean($text);
        $result = [];
        $this->put($result, 'name', $this->name($text));
        $this->put($result, 'email', $this->match($text, '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i'));
        $this->put($result, 'phone', $this->match($text, '/(?:\+?\d[\d ()-]{7,}\d)/'));
        $this->put($result, 'date_of_birth', $this->date($text, ['date of birth', 'dob', 'birth date']));
        $this->put($result, 'nationality', $this->lineValue($text, ['nationality', 'citizenship']));
        $this->put($result, 'address', $this->lineValue($text, ['address', 'present address', 'permanent address']));
        $this->put($result, 'department', $this->lineValue($text, ['department', 'functional area']));
        $this->put($result, 'designation', $this->lineValue($text, ['designation', 'job title', 'position', 'current position']));
        $this->put($result, 'employment_type', $this->lineValue($text, ['employment type', 'job type']));
        $this->put($result, 'qualification', $this->lineValue($text, ['qualification', 'education', 'academic qualification', 'degree']));
        $this->put($result, 'religion', $this->lineValue($text, ['religion']));
        $this->put($result, 'blood_group', $this->lineValue($text, ['blood group', 'blood type']));
        $this->put($result, 'marital_status', $this->lineValue($text, ['marital status']));
        $this->put($result, 'skills', $this->section($text, ['skills', 'technical skills', 'core competencies']));
        $this->put($result, 'experience_summary', $this->section($text, ['experience', 'work experience', 'professional experience', 'employment history']));
        $this->put($result, 'emergency_contact_name', $this->lineValue($text, ['emergency contact', 'contact person', 'reference name']));
        return $result;
    }

    protected function clean(string $text): string
    {
        return trim(preg_replace('/\n{3,}/u', "\n\n", preg_replace('/[ \t]+/u', ' ', $text) ?? $text) ?? $text);
    }

    protected function name(string $text): ?string
    {
        $value = $this->lineValue($text, ['full name', 'candidate name', 'name']);
        if ($value !== null) {
            return $value;
        }
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '' && ! str_contains($line, '@') && ! preg_match('/\d{5,}/', $line) && mb_strlen($line) <= 80) {
                return $line;
            }
        }
        return null;
    }

    protected function date(string $text, array $labels): ?string
    {
        $value = $this->lineValue($text, $labels);
        if ($value === null) {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function lineValue(string $text, array $labels): ?string
    {
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);
            foreach ($labels as $label) {
                if (preg_match('/^'.preg_quote($label, '/').'\s*[:\-]?\s*(.+)$/i', $line, $match)) {
                    return trim($match[1]);
                }
            }
        }
        return null;
    }

    protected function section(string $text, array $headings): ?string
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $start = null;
        foreach ($lines as $index => $line) {
            if (in_array(rtrim(mb_strtolower(trim($line)), ':'), $headings, true)) {
                $start = $index + 1;
                break;
            }
        }
        if ($start === null) {
            return null;
        }
        $values = [];
        $knownHeadings = ['summary', 'objective', 'experience', 'work experience', 'education', 'qualification', 'skills', 'references', 'personal details'];
        for ($index = $start; $index < count($lines); $index++) {
            $line = trim($lines[$index]);
            if (in_array(rtrim(mb_strtolower($line), ':'), $knownHeadings, true)) {
                break;
            }
            if ($line !== '') {
                $values[] = $line;
            }
        }
        return $values === [] ? null : implode(', ', array_slice($values, 0, 12));
    }

    protected function match(string $text, string $pattern): ?string
    {
        return preg_match($pattern, $text, $match) ? trim($match[0]) : null;
    }

    /** @param array<string, string> $result */
    protected function put(array &$result, string $key, ?string $value): void
    {
        if ($value !== null && trim($value) !== '') {
            $result[$key] = trim($value);
        }
    }
}
