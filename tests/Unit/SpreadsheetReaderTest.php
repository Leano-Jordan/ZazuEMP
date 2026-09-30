<?php

namespace Tests\Unit;

use App\Services\SpreadsheetReader;
use RuntimeException;
use Tests\TestCase;

class SpreadsheetReaderTest extends TestCase
{
    public function test_csv_is_read_into_headers_and_named_rows(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-csv-');
        file_put_contents($path, "Client Name,Contact,Phone\nABC Catering,John Smith,0821234567\n\nSecond Client,,0837654321\n");

        try {
            $result = app(SpreadsheetReader::class)->read($path, 'customers.csv', 'text/csv');

            $this->assertSame(['Client Name', 'Contact', 'Phone'], $result['Sheet 1']['headers']);
            $this->assertSame([
                [
                    'Client Name' => 'ABC Catering',
                    'Contact' => 'John Smith',
                    'Phone' => '0821234567',
                ],
                [
                    'Client Name' => 'Second Client',
                    'Contact' => '',
                    'Phone' => '0837654321',
                ],
            ], $result['Sheet 1']['rows']);
        } finally {
            @unlink($path);
        }
    }

    public function test_duplicate_and_blank_headers_are_made_safe_for_mapping(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-csv-');
        file_put_contents($path, "Name,,Name\nA,,B\n");

        try {
            $result = app(SpreadsheetReader::class)->read($path, 'sample.csv', 'text/csv');

            $this->assertSame(['Name', 'Column 2', 'Name 2'], $result['Sheet 1']['headers']);
            $this->assertSame([
                ['Name' => 'A', 'Column 2' => '', 'Name 2' => 'B'],
            ], $result['Sheet 1']['rows']);
        } finally {
            @unlink($path);
        }
    }

    public function test_unsupported_format_is_rejected_before_import_work(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'zazu-text-');
        file_put_contents($path, 'not a spreadsheet');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Unsupported spreadsheet format');

            app(SpreadsheetReader::class)->read($path, 'customers.xls');
        } finally {
            @unlink($path);
        }
    }
}
