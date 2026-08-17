<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ScopeReviewImportParser
{
    /**
     * Our target fields and the header keywords we try to auto-match against.
     */
    public const FIELD_KEYWORDS = [
        'source'           => ['source'],
        'entry_date'       => ['entry date', 'entry'],
        'platform'         => ['platform'],
        'location'         => ['location'],
        'project_name'     => ['project name', 'project'],
        'due_date'         => ['bid due', 'due date', 'due'],
        'estimator'        => ['estimator'],
        'duration'         => ['duration'],
        'bid_status'       => ['bid status', 'status'],
        'reason_to_ignore' => ['reason to ignore', 'reason'],
        'bid_stage'        => ['bid stage', 'stage'],
        'project_number'   => ['project #', 'project number', 'job number', 'job #'],
        'uploaded_in_oh'   => ['uploaded in oh', 'in oh'],
        'notes'            => ['notes', 'note'],
    ];

    /**
     * Exact-match header aliases for short/ambiguous columns that would cause
     * false positives under substring matching (e.g. "OH", "#").
     * Matched (case-insensitive, trimmed) before the substring keywords above.
     */
    public const FIELD_EXACT_ALIASES = [
        'uploaded_in_oh' => ['oh'],
        'project_number' => ['#'],
    ];

    public function listSheetNames(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);

        return $reader->listWorksheetNames($path);
    }

    /**
     * Parse a single sheet into a header row + raw data rows (arrays of cell strings),
     * plus the hyperlink target for the project name column, if any.
     */
    public function parseSheet(string $path, string $sheetName): array
    {
        $spreadsheet = IOFactory::createReaderForFile($path)->load($path);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            throw new \InvalidArgumentException("Sheet \"{$sheetName}\" not found.");
        }

        $rows = $sheet->toArray(null, true, true, false);
        $header = array_shift($rows) ?? [];
        $header = array_map(fn($h) => trim((string) $h), $header);

        // Filter out fully-empty rows.
        $rows = array_values(array_filter($rows, function ($row) {
            return collect($row)->filter(fn($cell) => trim((string) $cell) !== '')->isNotEmpty();
        }));

        // Extract hyperlinks for the project name column, if we can find it.
        $projectNameColIndex = null;
        foreach ($header as $i => $h) {
            if (str_contains(strtolower($h), 'project name') || strtolower($h) === 'project') {
                $projectNameColIndex = $i;
                break;
            }
        }

        $links = [];
        if ($projectNameColIndex !== null) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($projectNameColIndex + 1);
            foreach ($rows as $rowIndex => $row) {
                $cell = $sheet->getCell($colLetter . ($rowIndex + 2)); // +2: header row + 1-indexed
                $hyperlink = $cell->getHyperlink();
                if ($hyperlink && $hyperlink->getUrl()) {
                    $links[$rowIndex] = $hyperlink->getUrl();
                }
            }
        }

        return [
            'header' => $header,
            'rows' => $rows,
            'project_link_column_index' => $projectNameColIndex,
            'project_links' => $links,
        ];
    }

    /**
     * Best-effort auto-mapping of sheet headers to our target fields.
     * Returns [field => column_index] for matched fields.
     */
    public function autoMapColumns(array $header): array
    {
        $mapping = [];

        // Pass 1: exact-match aliases for short/ambiguous headers (e.g. "OH", "#").
        foreach ($header as $index => $label) {
            $normalized = strtolower(trim($label));
            if ($normalized === '') {
                continue;
            }

            foreach (self::FIELD_EXACT_ALIASES as $field => $aliases) {
                if (isset($mapping[$field])) {
                    continue;
                }
                if (in_array($normalized, $aliases, true) && !in_array($index, $mapping, true)) {
                    $mapping[$field] = $index;
                    break;
                }
            }
        }

        // Pass 2: substring keyword matching for descriptive headers.
        foreach ($header as $index => $label) {
            $normalized = strtolower(trim($label));
            if ($normalized === '' || in_array($index, $mapping, true)) {
                continue;
            }

            foreach (self::FIELD_KEYWORDS as $field => $keywords) {
                if (isset($mapping[$field])) {
                    continue;
                }
                foreach ($keywords as $keyword) {
                    if (str_contains($normalized, $keyword)) {
                        $mapping[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Try to match a free-text estimator name (e.g. "RAJAT") to an existing User
     * by first/last name, case-insensitive, whole-word match.
     */
    public function matchEstimator(string $name, $estimators): ?User
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $needle = strtolower($name);

        foreach ($estimators as $estimator) {
            $parts = array_map('strtolower', preg_split('/\s+/', trim($estimator->name)));
            if (in_array($needle, $parts, true)) {
                return $estimator;
            }
        }

        return null;
    }

    /**
     * Parse a BID STATUS-style cell into [decision, project_type].
     * Handles combined values like "Yes - NON MU", "Yes - MU", plus
     * Skip/No -> not_in_scope, Pending/blank -> null (still pending).
     */
    public function parseBidStatus(?string $value): array
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return [null, null, false];
        }

        $normalized = strtolower($raw);

        if (str_contains($normalized, 'pending')) {
            return [null, null, true];
        }

        if (str_starts_with($normalized, 'yes')) {
            $type = null;
            if (str_contains($normalized, 'non mu') || str_contains($normalized, 'non-mu') || str_contains($normalized, 'nonmu')) {
                $type = 'NON_MU';
            } elseif (str_contains($normalized, 'mu')) {
                $type = 'MU';
            }
            return ['approved', $type, true];
        }

        if (str_contains($normalized, 'skip')) {
            return ['skipped', null, true];
        }

        if ($normalized === 'no') {
            return ['not_in_scope', null, true];
        }

        if (str_contains($normalized, 'rfi')) {
            return ['rfi_requested', null, true];
        }

        // Unrecognized value — caller should flag this row for manual review.
        return [null, null, false];
    }

    public function parseDate($value): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            // Excel serial date
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
