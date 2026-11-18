<?php

namespace Tests\Feature;

use App\Services\SignedOfflineLicenseVerifier;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ZazuIssueLicenseCommandTest extends TestCase
{
    private string $keyFile;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('PHP Sodium is required for signed licence tests.');
        }

        $keypair = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keypair);
        $publicKey = sodium_crypto_sign_publickey($keypair);
        $this->publicKey = base64_encode($publicKey);
        $this->keyFile = tempnam(sys_get_temp_dir(), 'zazu-license-key-');

        file_put_contents($this->keyFile, base64_encode($secretKey));
        config(['zazu.licensing.public_key' => $this->publicKey]);
    }

    protected function tearDown(): void
    {
        if (isset($this->keyFile) && is_file($this->keyFile)) {
            unlink($this->keyFile);
        }

        parent::tearDown();
    }

    public function test_command_issues_a_token_accepted_by_the_existing_business_verifier(): void
    {
        $exitCode = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'advanced',
            '--private-key-file' => $this->keyFile,
            '--starts-at' => '2026-11-19T00:00:00+02:00',
            '--expires-at' => '2027-11-18T23:59:59+02:00',
            '--features' => '{"offline_core":true}',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertMatchesRegularExpression('/^TOKEN: [A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/m', Artisan::output());

        preg_match('/^TOKEN: ([A-Za-z0-9_-]+\.[A-Za-z0-9_-]+)$/m', Artisan::output(), $matches);
        $this->assertArrayHasKey(1, $matches);

        $verified = app(SignedOfflineLicenseVerifier::class)->verifyForBusiness($matches[1], 42);

        $this->assertSame(42, $verified['payload']['business_id']);
        $this->assertSame('advanced', $verified['payload']['plan']);
        $this->assertSame('active', $verified['payload']['status']);
        $this->assertSame(['offline_core' => true], $verified['payload']['features']);
        $this->assertSame('2027-11-18T23:59:59+02:00', $verified['payload']['expires_at']);
    }

    public function test_command_requires_an_expiry_or_an_explicit_never_expires_choice(): void
    {
        $exitCode = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'basic',
            '--private-key-file' => $this->keyFile,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Supply an expiry timestamp', Artisan::output());
    }

    public function test_revocation_requires_the_existing_license_id(): void
    {
        $exitCode = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'advanced',
            '--private-key-file' => $this->keyFile,
            '--status' => 'revoked',
            '--never-expires' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Revocation requires the existing licence ID', Artisan::output());
    }

    public function test_command_refuses_a_signing_key_stored_inside_the_project(): void
    {
        $projectKey = storage_path('app/zazu-license-test-private.key');
        file_put_contents($projectKey, file_get_contents($this->keyFile));

        try {
            $exitCode = Artisan::call('zazu:license:issue', [
                'business_id' => 42,
                'plan' => 'advanced',
                '--private-key-file' => $projectKey,
                '--expires-at' => '2027-11-18T23:59:59+02:00',
            ]);

            $this->assertSame(1, $exitCode);
            $this->assertStringContainsString('stored outside the Zazu project', Artisan::output());
        } finally {
            if (is_file($projectKey)) {
                unlink($projectKey);
            }
        }
    }

    public function test_command_refuses_a_private_key_that_does_not_match_the_configured_public_key(): void
    {
        $otherKeypair = sodium_crypto_sign_keypair();
        config(['zazu.licensing.public_key' => base64_encode(sodium_crypto_sign_publickey($otherKeypair))]);

        $exitCode = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'advanced',
            '--private-key-file' => $this->keyFile,
            '--expires-at' => '2027-11-18T23:59:59+02:00',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('does not match the configured Zazu public key', Artisan::output());
    }

    public function test_command_rejects_invalid_calendar_dates_and_non_object_feature_metadata(): void
    {
        $invalidDateExit = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'advanced',
            '--private-key-file' => $this->keyFile,
            '--expires-at' => '2026-02-30T23:59:59+02:00',
        ]);

        $this->assertSame(1, $invalidDateExit);
        $this->assertStringContainsString('not a valid calendar date', Artisan::output());

        $invalidFeaturesExit = Artisan::call('zazu:license:issue', [
            'business_id' => 42,
            'plan' => 'advanced',
            '--private-key-file' => $this->keyFile,
            '--expires-at' => '2027-11-18T23:59:59+02:00',
            '--features' => '[]',
        ]);

        $this->assertSame(1, $invalidFeaturesExit);
        $this->assertStringContainsString('must be a JSON object', Artisan::output());
    }
}
