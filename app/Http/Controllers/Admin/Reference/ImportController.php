<?php

namespace App\Http\Controllers\Admin\Reference;

use App\Http\Controllers\Controller;
use App\Models\ReferenceInfo;
use App\Models\ReferenceLogin;
use App\Models\ReferenceSkippedGc;
use App\Services\ScopeReviewImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Shared Excel import for the three Reference tables. One flow, parameterised by
 * $type (logins | skipped-gcs | infos). Upload -> preview -> commit.
 */
class ImportController extends Controller
{
    private const STORAGE_DIR = 'reference-imports';

    /**
     * Per-type config: model, expected columns (in order), and labels.
     */
    private function config(string $type): array
    {
        return match ($type) {
            'logins' => [
                'model'   => ReferenceLogin::class,
                'label'   => 'Log Ins',
                'columns' => ['platform', 'email', 'password'],
                'headers' => ['Platform', 'Email', 'Password'],
            ],
            'skipped-gcs' => [
                'model'   => ReferenceSkippedGc::class,
                'label'   => 'Skipped GCs',
                'columns' => ['status', 'gc_name', 'note'],
                'headers' => ['Status', 'GC Name', 'Note'],
            ],
            'infos' => [
                'model'   => ReferenceInfo::class,
                'label'   => 'Infos',
                'columns' => ['item', 'description', 'status'],
                'headers' => ['Item', 'Description', 'Status'],
            ],
            default => abort(404),
        };
    }

    public function create(string $type)
    {
        $config = $this->config($type);

        return view('admin.reference.import.upload', [
            'type'   => $type,
            'label'  => $config['label'],
            'headers' => $config['headers'],
        ]);
    }

    public function upload(Request $request, string $type, ScopeReviewImportParser $parser)
    {
        $config = $this->config($type);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $path  = $request->file('file')->store(self::STORAGE_DIR, 'local');
        $token = basename($path);
        $abs   = Storage::disk('local')->path($path);

        // CSV has no worksheet names; only xlsx/xls do. Fall back to the first sheet.
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if ($ext === 'csv') {
            $parsed = $this->parseCsv($abs);
        } else {
            $sheetNames = $parser->listSheetNames($abs);
            $sheet = $request->input('sheet', $sheetNames[0]);
            $parsed = $parser->parseSheet($abs, $sheet);
        }

        // Map by header name when possible; otherwise fall back to column position.
        $rows = $this->mapRows($parsed['header'], $parsed['rows'], $config['columns']);

        return view('admin.reference.import.review', [
            'type'    => $type,
            'label'   => $config['label'],
            'headers' => $config['headers'],
            'columns' => $config['columns'],
            'rows'    => $rows,
            'token'   => $token,
        ]);
    }

    public function commit(Request $request, string $type)
    {
        $config = $this->config($type);
        $model  = $config['model'];

        $validated = $request->validate([
            'rows'   => 'required|array|min:1',
            'rows.*' => 'array',
        ]);

        $imported = 0;

        DB::transaction(function () use ($validated, $model, $config, $type, &$imported) {
            $sort = $model::max('sort_order') ?? 0;

            foreach ($validated['rows'] as $row) {
                // Skip a row that has no meaningful content.
                $hasContent = collect($config['columns'])->contains(fn ($c) => trim((string) ($row[$c] ?? '')) !== '');
                if (!$hasContent) {
                    continue;
                }

                $data = [];
                foreach ($config['columns'] as $c) {
                    $val = trim((string) ($row[$c] ?? ''));
                    $data[$c] = $val === '' ? null : $val;
                }

                // Required field per type must be present.
                $requiredField = $config['columns'][0] === 'status' ? 'gc_name' : $config['columns'][0];
                if (($data[$requiredField] ?? null) === null) {
                    continue;
                }

                // Default status for skipped GCs if blank/unknown.
                if ($type === 'skipped-gcs') {
                    $data['status'] = in_array($data['status'] ?? '', ReferenceSkippedGc::STATUSES, true)
                        ? $data['status']
                        : 'SKIP';
                }

                $data['sort_order'] = ++$sort;
                $model::create($data);
                $imported++;
            }
        });

        // Clean up the uploaded file.
        if ($request->filled('token')) {
            Storage::disk('local')->delete(self::STORAGE_DIR . '/' . $request->token);
        }

        return redirect()->route("admin.reference.{$type}.index")
            ->with('success', "{$imported} row(s) imported.");
    }

    /**
     * Parse a CSV into the same {header, rows} shape parseSheet returns.
     */
    private function parseCsv(string $path): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows) ?? []);

        // Drop fully-empty rows.
        $rows = array_values(array_filter($rows, function ($row) {
            return collect($row)->filter(fn ($cell) => trim((string) $cell) !== '')->isNotEmpty();
        }));

        return ['header' => $header, 'rows' => $rows, 'links' => []];
    }

    /**
     * Build associative rows keyed by our target columns. Matches Excel headers
     * by name (case-insensitive) when found, else falls back to column order.
     */
    private function mapRows(array $header, array $rows, array $columns): array
    {
        // header index -> our column, by name match
        $byName = [];
        foreach ($header as $i => $h) {
            $norm = strtolower(trim((string) $h));
            foreach ($columns as $c) {
                $target = strtolower(str_replace('_', ' ', $c));
                if ($norm === $target || $norm === str_replace(' ', '', $target)) {
                    $byName[$c] = $i;
                }
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $mapped = [];
            foreach ($columns as $pos => $c) {
                $index = $byName[$c] ?? $pos; // name match, else positional
                $mapped[$c] = trim((string) ($row[$index] ?? ''));
            }
            // Drop fully-empty rows.
            if (collect($mapped)->contains(fn ($v) => $v !== '')) {
                $out[] = $mapped;
            }
        }

        return $out;
    }
}
