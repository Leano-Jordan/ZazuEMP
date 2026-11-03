<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_reads_rows_from_the_first_workbook_sheet_without_writing_customers(): void
    {
        $business = Business::create([
            'name' => 'Import Test Business',
            'slug' => 'import-test-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->post(route('customers.import.preview'), [
            'spreadsheet' => UploadedFile::fake()->createWithContent(
                'customers.csv',
                "Customer Name,Contact\nNew Prospect,Jordan Smith\n",
            ),
        ]);

        $response->assertOk();
        $response->assertSee('New Prospect');
        $response->assertViewHas('preview', fn (array $preview): bool =>
            $preview['summary']['total'] === 1
            && $preview['rows'][0]['customer']['name'] === 'New Prospect'
            && $preview['rows'][0]['primary_contact']['name'] === 'Jordan Smith'
        );

        $this->assertDatabaseMissing('customers', [
            'business_id' => $business->id,
            'name' => 'New Prospect',
        ]);
    }
}
