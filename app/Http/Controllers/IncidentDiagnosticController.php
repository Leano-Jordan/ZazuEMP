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

        $incident = $this->findDatabaseIncident($requestId);

        abort_if($incident === null, 404, 'No matching database incident record is available.');

        $filename = 'zazu-db001-'.$requestId.'.json';

        return response()
            ->json($incident)
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'no-store, private');
    }

    private function findDatabaseIncident(string $requestId): ?array
    {
        $paths = glob(storage_path('logs/laravel*.log')) ?: [];

        foreach ($paths as $path) {
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            try {
                $file = new SplFileObject($path, 'r');
            } catch (Throwable) {
                continue;
            }

            while (!$file->eof()) {
                $line = $file->fgets();

                if (!is_string($line) || !str_contains($line, $requestId)) {
                    continue;
                }

                $jsonStart = strpos($line, '{');

                if ($jsonStart === false) {
                    continue;
                }

                try {
                    $decoded = json_decode(substr($line, $jsonStart), true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    continue;
                }

                if (!is_array($decoded)) {
                    continue;
                }

                $context = is_array($decoded['context'] ?? null) ? $decoded['context'] : $decoded;

                if (($context['request_id'] ?? null) !== $requestId || ($context['error_code'] ?? null) !== 'DB-001') {
                    continue;
                }

                return $this->allowlistedFields($context);
            }
        }

        return null;
    }

    private function allowlistedFields(array $context): array
    {
        $fields = [
            'request_id',
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
