<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_missing_route_uses_branded_error_surface_and_request_reference(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('We could not find that page.');
        $response->assertSee('Reference');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_unexpected_database_failure_uses_branded_500_surface_without_leaking_details(): void
    {
        Route::get('/__zazu-test-missing-table', fn () => DB::table('__zazu_missing_table__')->count());

        $response = $this->get('/__zazu-test-missing-table');

        $response->assertStatus(500);
        $response->assertSee('Zazu could not complete that request.');
        $response->assertSee('Reference');
        $response->assertDontSee('__zazu_missing_table__');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_validation_failure_keeps_standard_redirect_and_field_errors(): void
    {
        Route::post('/__zazu-test-validation', fn () => request()->validate([
            'name' => ['required', 'string'],
        ]));

        $response = $this->from('/dashboard')->post('/__zazu-test-validation', []);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('name');
        $response->assertNotSame(500, $response->status());
    }
}
