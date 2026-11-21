<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SyncTrackedQueryBuilderWriteGuardTest extends TestCase
{
    /**
     * These aggregates have server-side revision listeners. Query-builder
     * mass writes bypass Eloquent model events and can leave offline snapshots
     * falsely current.
     *
     * @var list<string>
     */
    private const TRACKED_TABLES = [
        'customers',
        'events',
        'quotes',
        'business_capabilities',
        'event_preparation_items',
        'suppliers',
        'purchase_orders',
        'invoices',
        'finance_expenses',
        'inventory_items',
        'assets',
        'event_costs',
        'customer_contacts',
        'event_requirements',
        'quote_versions',
        'quote_items',
        'invoice_items',
        'payments',
        'purchase_order_items',
        'purchase_order_receipts',
        'inventory_movements',
        'asset_allocations',
    ];

    public function test_guard_detects_a_query_builder_mass_write_fixture(): void
    {
        $violations = $this->findViolations(
            "DB::table('customers')->where('id', 10)->update(['name' => 'Changed']);",
            'fixture.php',
        );

        $this->assertSame(['fixture.php: customers -> update'], $violations);
    }

    public function test_application_contains_no_query_builder_mass_writes_to_revision_tracked_tables(): void
    {
        $applicationPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($applicationPath, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        $violations = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if ($source === false) {
                continue;
            }

            $relativePath = str_replace(
                dirname(__DIR__, 2).DIRECTORY_SEPARATOR,
                '',
                $file->getPathname(),
            );
            $violations = array_merge($violations, $this->findViolations($source, $relativePath));
        }

        $this->assertSame(
            [],
            $violations,
            "Query-builder mass writes bypass tracked Eloquent revision events:\n".implode("\n", $violations),
        );
    }

    /**
     * @return list<string>
     */
    private function findViolations(string $source, string $path): array
    {
        $tablePattern = implode('|', array_map(
            static fn (string $table): string => preg_quote($table, '/'),
            self::TRACKED_TABLES,
        ));
        $pattern = '/\bDB::(?:connection\s*\([^;]*?\)\s*->\s*)?table\s*\(\s*[\'"]('.$tablePattern.')[\'"]\s*\)(?:(?!;).)*?->\s*(update|delete|increment|decrement|upsert|insertOrIgnore|updateOrInsert|truncate)\s*\(/s';

        preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);

        return array_map(
            static fn (array $match): string => $path.': '.$match[1].' -> '.$match[2],
            $matches,
        );
    }
}
