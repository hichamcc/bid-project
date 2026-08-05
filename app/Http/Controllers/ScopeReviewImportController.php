<?php

namespace App\Http\Controllers;

use App\Models\ScopeReview;
use App\Models\User;
use App\Services\ScopeReviewImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScopeReviewImportController extends Controller
{
    private const STORAGE_DIR = 'scope-review-imports';

    public function __construct(private ScopeReviewImportParser $parser)
    {
        // Import is an admin-only tool.
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('scope-review.import.upload');
    }

    /**
     * Step 1: accept the uploaded file, list its sheets.
     * If there's only one sheet, go straight to parsing it.
     */
    public function upload(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $path = $request->file('file')->store(self::STORAGE_DIR, 'local');
        $absolutePath = Storage::disk('local')->path($path);

        $sheetNames = $this->parser->listSheetNames($absolutePath);

        if (count($sheetNames) === 1) {
            return redirect()->route('scope-review.import.review', [
                'token' => basename($path),
                'sheet' => $sheetNames[0],
            ]);
        }

        return view('scope-review.import.select-sheet', [
            'token' => basename($path),
            'sheetNames' => $sheetNames,
        ]);
    }

    /**
     * Step 2: parse the chosen sheet, auto-map columns/estimators/status,
     * show the review/config screen. No DB writes yet.
     */
    public function review(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'token' => 'required|string',
            'sheet' => 'required|string',
        ]);

        $path = $this->resolveStoredPath($request->token);
        $parsed = $this->parser->parseSheet($path, $request->sheet);

        $columnMapping = $this->parser->autoMapColumns($parsed['header']);
        $estimators = User::whereIn('role', ['estimator', 'head_estimator'])->orderBy('name')->get();

        $previewRows = [];
        foreach ($parsed['rows'] as $rowIndex => $row) {
            $get = fn($field) => isset($columnMapping[$field]) ? ($row[$columnMapping[$field]] ?? null) : null;

            [$decision, $projectType, $statusRecognized] = $this->parser->parseBidStatus($get('bid_status'));

            $estimatorName = trim((string) $get('estimator'));
            $matchedEstimator = $estimatorName !== '' ? $this->parser->matchEstimator($estimatorName, $estimators) : null;

            $previewRows[] = [
                'row_index'          => $rowIndex,
                'source'             => trim((string) $get('source')) ?: null,
                'entry_date'         => optional($this->parser->parseDate($get('entry_date')))->format('Y-m-d'),
                'platform'           => trim((string) $get('platform')) ?: null,
                'location'           => trim((string) $get('location')) ?: null,
                'project_name'       => trim((string) $get('project_name')) ?: null,
                'project_link'       => $parsed['project_links'][$rowIndex] ?? null,
                'due_date'           => optional($this->parser->parseDate($get('due_date')))->format('Y-m-d'),
                'duration'           => trim((string) $get('duration')) ?: null,
                'reason_to_ignore'   => trim((string) $get('reason_to_ignore')) ?: null,
                'bid_stage'          => trim((string) $get('bid_stage')) ?: null,
                'project_number'     => trim((string) $get('project_number')) ?: null,
                'uploaded_in_oh'     => in_array(strtolower(trim((string) $get('uploaded_in_oh'))), ['yes', 'y', '1', 'true'], true),
                'notes'              => trim((string) $get('notes')) ?: null,
                'estimator_name_raw' => $estimatorName ?: null,
                'estimator_id'       => $matchedEstimator?->id,
                'decision'           => $decision,
                'project_type'       => $projectType,
                'status_raw'         => trim((string) $get('bid_status')) ?: null,
                'status_recognized'  => $statusRecognized,
            ];
        }

        return view('scope-review.import.review', [
            'token' => $request->token,
            'sheet' => $request->sheet,
            'header' => $parsed['header'],
            'columnMapping' => $columnMapping,
            'previewRows' => $previewRows,
            'estimators' => $estimators,
            'fieldKeywords' => ScopeReviewImportParser::FIELD_KEYWORDS,
        ]);
    }

    /**
     * Step 3: commit the (possibly corrected) rows submitted from the review form.
     */
    public function commit(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'rows'                       => 'required|array|min:1',
            'rows.*.include'             => 'nullable|boolean',
            'rows.*.source'              => 'nullable|string|max:255',
            'rows.*.entry_date'          => 'required|date',
            'rows.*.platform'            => 'nullable|string|max:255',
            'rows.*.location'            => 'nullable|string|max:255',
            'rows.*.project_name'        => 'required|string|max:255',
            'rows.*.project_link'        => 'nullable|string|max:2048',
            'rows.*.due_date'            => 'nullable|date',
            'rows.*.duration'            => 'nullable|string|max:255',
            'rows.*.reason_to_ignore'    => 'nullable|string|max:255',
            'rows.*.bid_stage'           => 'nullable|string|max:255',
            'rows.*.project_number'      => 'nullable|string|max:255',
            'rows.*.uploaded_in_oh'      => 'nullable|boolean',
            'rows.*.notes'               => 'nullable|string',
            'rows.*.estimator_id'        => 'nullable|exists:users,id',
            'rows.*.decision'            => 'nullable|in:approved,rfi_requested,not_in_scope,skipped',
            'rows.*.project_type'        => 'nullable|in:MU,NON_MU',
        ]);

        $imported = 0;
        $blank = fn($value) => ($value ?? '') === '' ? null : $value;

        DB::transaction(function () use ($validated, $request, $blank, &$imported) {
            foreach ($validated['rows'] as $key => $row) {
                if (!$request->boolean("rows.{$key}.include", true)) {
                    continue;
                }

                ScopeReview::create([
                    'entry_date'             => $row['entry_date'],
                    'source'                 => $blank($row['source'] ?? null),
                    'platform'               => $blank($row['platform'] ?? null),
                    'project_name'           => $row['project_name'],
                    'due_date'               => $blank($row['due_date'] ?? null),
                    'project_link'           => $blank($row['project_link'] ?? null),
                    'location'               => $blank($row['location'] ?? null),
                    'reason_to_ignore'       => $blank($row['reason_to_ignore'] ?? null),
                    'bid_stage'              => $blank($row['bid_stage'] ?? null),
                    'assigned_estimator_id'  => $blank($row['estimator_id'] ?? null),
                    'project_type'           => $blank($row['project_type'] ?? null),
                    'decision'               => $blank($row['decision'] ?? null),
                    'duration'               => $blank($row['duration'] ?? null),
                    'uploaded_in_oh'         => !empty($row['uploaded_in_oh']),
                    'estimator_notes'        => $blank($row['notes'] ?? null),
                    'reviewed_at'            => !empty($row['decision']) ? now() : null,
                    'project_number'         => $blank($row['project_number'] ?? null),
                    'created_by'             => Auth::id(),
                ]);

                $imported++;
            }
        });

        if ($request->filled('token')) {
            Storage::disk('local')->delete(self::STORAGE_DIR . '/' . basename($request->token));
        }

        return redirect()->route('scope-review.index')
            ->with('success', "Imported {$imported} scope review(s).");
    }

    private function resolveStoredPath(string $token): string
    {
        $filename = basename($token);
        $relative = self::STORAGE_DIR . '/' . $filename;

        if (!Storage::disk('local')->exists($relative)) {
            abort(404, 'Uploaded file not found or has expired. Please upload again.');
        }

        return Storage::disk('local')->path($relative);
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
