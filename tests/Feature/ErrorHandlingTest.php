<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test the same non-debug HTML error contract used in production.
        config()->set('app.debug', false);
    }
    public function test_missing_route_uses_branded_error_surface_and_request_reference(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound();
        $response->assertSee('We could not find that page.');
        $response->assertSee('Reference');
        $this->assertNotEmpty($response->headers->get('X-Zazu-Request-Id'));
    }

    public function test_production_500_view_renders_a_safe_traceable_error_surface(): void
    {
        $response = $this->view('errors.500', [
            'requestId' => 'test-request-id',
            'code' => 500,
        ]);

        $response->assertSee('Zazu could not complete that request.');
        $response->assertSee('Reference test-request-id');
        $response->assertDontSee('Sensitive database/table details');
    }

    public function test_production_500_view_is_present_in_the_application_error_views(): void
    {
        $this->assertFileExists(resource_path('views/errors/500.blade.php'));
        $this->assertTrue(File::exists(resource_path('views/errors/layout.blade.php')));
    }

    public function test_validation_failure_keeps_standard_redirect_and_field_errors(): void
    {
        Route::post('/__zazu-test-validation', fn () => request()->validate([
            'name' => ['required', 'string'],
        ]))->middleware('web');

        $response = $this->from('/dashboard')->post('/__zazu-test-validation', []);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHasErrors('name');
    }
}
