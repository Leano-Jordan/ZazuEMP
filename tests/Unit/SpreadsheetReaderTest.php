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


    public function test_xlsx_is_read_without_changing_zazu_business_records(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('PHP ZIP extension is not available.');
        }

        $path = tempnam(sys_get_temp_dir(), 'zazu-xlsx-');
        $zip = new \ZipArchive();

        $this->assertSame(true, $zip->open($path));

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Customers" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><si><t>Client Name</t></si><si><t>Contact</t></si><si><t>ABC Catering</t></si><si><t>John Smith</t></si></sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row><row r="2"><c r="A2" t="s"><v>2</v></c><c r="C2" t="inlineStr"><is><t>0821234567</t></is></c><c r="B2" t="s"><v>3</v></c></row></sheetData></worksheet>');
        $zip->close();

        try {
            $result = app(\App\Services\SpreadsheetReader::class)->read(
                $path,
                'customers.xlsx',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );

            $this->assertArrayHasKey('Customers', $result);
            $this->assertSame(['Client Name', 'Contact', 'Column 3'], $result['Customers']['headers']);
            $this->assertSame([
                [
                    'Client Name' => 'ABC Catering',
                    'Contact' => 'John Smith',
                    'Column 3' => '0821234567',
                ],
            ], $result['Customers']['rows']);
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
