<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use SplFileObject;
use Throwable;

class IncidentDiagnosticController extends Controller
{
    public function show(string $requestId): JsonResponse
    {
        abort_unless(Str::isUuid($requestId), 404);

        // Prefer the independent ledger; legacy Laravel logs remain a fallback
        // for incidents captured before the ledger was introduced.
        $incident = ZazuIncidentLedger::find($requestId) ?? $this->findDatabaseIncident($requestId);

        abort_if($incident === null, 404, 'No matching database incident record is available.');

        $filename = 'zazu-db001-'.$requestId.'.json';

        return response()
            ->json($incident)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'no-store, private');
    }

    private function findDatabaseIncident(string $requestId): ?array
    {
        foreach (glob(storage_path('logs/laravel*.log')) ?: [] as $path) {
            $incident = $this->findIncidentInFile($path, $requestId);

            if ($incident !== null) {
                return $incident;
            }
        }

        return null;
    }

    private function findIncidentInFile(string $path, string $requestId): ?array
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        try {
            $file = new SplFileObject($path, 'r');
        } catch (Throwable) {
            return null;
        }

        while (!$file->eof()) {
            $context = $this->matchingIncidentContext($file->fgets(), $requestId);

            if ($context !== null) {
                return $this->allowlistedFields($context);
            }
        }

        return null;
    }

    private function matchingIncidentContext(mixed $line, string $requestId): ?array
    {
        if (!is_string($line) || !str_contains($line, $requestId)) {
            return null;
        }

        $jsonStart = strpos($line, '{');

        if ($jsonStart === false) {
            return null;
        }

        try {
            $decoded = json_decode(substr($line, $jsonStart), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($decoded)) {
            return null;
        }

        $context = is_array($decoded['context'] ?? null) ? $decoded['context'] : $decoded;

        return ($context['request_id'] ?? null) === $requestId && ($context['error_code'] ?? null) === 'DB-001'
            ? $context
            : null;
    }

    private function allowlistedFields(array $context): array
    {
        $fields = [
            'request_id',
            'failure_fingerprint',
            'failure_fingerprint_version',
            'error_code',
            'category',
            'severity',
            'http_status',
            'exception_class',
            'originating_component',
            'route_name',
            'http_method',
            'timestamp',
            'line',
            'database_connection',
            'database_driver',
            'database_sqlstate',
            'database_driver_code',
            'database_operation',
            'database_failure_class',
        ];

        $safe = [];

        foreach ($fields as $field) {
            $value = $context[$field] ?? null;

            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $safe[$field] = $value;
            }
        }

        if (isset($context['file']) && is_string($context['file'])) {
            $safe['source_file'] = basename($context['file']);
        }

        return $safe;
    }
}
