<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\SyncDevice;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationCausalRevisionResolver;
use App\Support\Offline\SyncMutationRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncMutationCausalRevisionResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_causal_revision_resolver_follows_long_keep_local_rebase_chains(): void
    {
        $business = Business::create([
            'name' => 'Causal Chain Business',
            'slug' => 'causal-chain-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Causal Chain Customer',
        ]);
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Causal chain phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'causal-chain-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');
        $recorder = app(SyncMutationRecorder::class);
        $chain = [];

        for ($index = 0; $index < 12; $index++) {
            $chain[] = $recorder->record(
                $device,
                'customer',
                $identity->entity_uuid,
                'update',
                [
                    'local_id' => $identity->entity_uuid,
                    'expected_revision' => 1,
                    'record' => ['name' => 'Rebased edit '.($index + 1)],
                ],
            );
        }

        foreach ($chain as $index => $mutation) {
            if (! isset($chain[$index + 1])) {
                $mutation->update([
                    'status' => 'applied',
                    'result_revision' => 14,
                    'applied_at' => now(),
                ]);
                continue;
            }

            $mutation->update([
                'status' => 'discarded',
                'superseded_by_mutation_id' => $chain[$index + 1]->mutation_id,
            ]);
        }

        $next = $recorder->record(
            $device,
            'customer',
            $identity->entity_uuid,
            'update',
            [
                'local_id' => $identity->entity_uuid,
                'predecessor_mutation_id' => $chain[0]->mutation_id,
                'record' => ['name' => 'Edit after twelve rebases'],
            ],
        );

        $this->assertSame(14, app(SyncMutationCausalRevisionResolver::class)->expectedRevision($next));
    }
}
