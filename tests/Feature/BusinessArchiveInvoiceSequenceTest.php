<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\BusinessArchiveService;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessArchiveInvoiceSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_archive_preserves_the_invoice_number_sequence(): void
    {
        $issuedAt = now();
        $period = $issuedAt->format('Ym');

        $source = Business::create([
            'name' => 'Invoice Sequence Source',
            'slug' => 'invoice-sequence-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        DB::table('invoice_number_sequences')->insert([
            'business_id' => $source->id,
            'period' => $period,
            'next_number' => 42,
            'created_at' => $issuedAt,
            'updated_at' => $issuedAt,
        ]);

        $archive = app(BusinessArchiveService::class)->download($source);
        $target = Business::create([
            'name' => 'Invoice Sequence Target',
            'slug' => 'invoice-sequence-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        try {
            app(BusinessArchiveService::class)->restore($archive, $target);
        } finally {
            @unlink($archive);
        }

        $nextNumber = DB::transaction(
            fn (): string => app(InvoiceNumberGenerator::class)->nextForBusiness($target->id, $issuedAt),
        );

        $this->assertSame('INV-'.$period.'-000042', $nextNumber);
        $this->assertDatabaseHas('invoice_number_sequences', [
            'business_id' => $target->id,
            'period' => $period,
            'next_number' => 43,
        ]);
    }

    public function test_legacy_business_archive_without_invoice_sequence_remains_restorable(): void
    {
        $source = Business::create([
            'name' => 'Legacy Archive Source',
            'slug' => 'legacy-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($archive) === true);
        $data = json_decode((string) $zip->getFromName('data.json'), true, 512, JSON_THROW_ON_ERROR);
        unset($data['invoice_number_sequences']);
        $zip->deleteName('data.json');
        $zip->addFromString('data.json', json_encode($data, JSON_THROW_ON_ERROR));
        $zip->close();

        $target = Business::create([
            'name' => 'Legacy Archive Target',
            'slug' => 'legacy-archive-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        try {
            $result = $service->restore($archive, $target);
        } finally {
            @unlink($archive);
        }

        $this->assertSame(29, $result['tables']);
        $this->assertDatabaseMissing('invoice_number_sequences', [
            'business_id' => $target->id,
        ]);
    }
}
