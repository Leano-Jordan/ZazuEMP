<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TOKEN_IDLE_EXPIRY_DAYS = 180;

    public function up(): void
    {
        Schema::table('sync_devices', function (Blueprint $table): void {
            $table->timestamp('token_expires_at')->nullable()->after('last_seen_at');
            $table->index(['status', 'token_expires_at'], 'sync_devices_token_expiry_index');
        });

        DB::table('sync_devices')
            ->select(['id', 'metadata', 'last_seen_at', 'created_at'])
            ->where('status', 'active')
            ->orderBy('id')
            ->chunkById(100, function ($devices): void {
                foreach ($devices as $device) {
                    $metadata = is_array($device->metadata)
                        ? $device->metadata
                        : json_decode((string) $device->metadata, true);

                    if (! is_array($metadata) || empty($metadata['sync_token_hash'])) {
                        continue;
                    }

                    $activity = $device->last_seen_at ?: $device->created_at;

                    if (! $activity) {
                        continue;
                    }

                    $expiresAt = Carbon::parse($activity)
                        ->addDays(self::TOKEN_IDLE_EXPIRY_DAYS);

                    DB::table('sync_devices')
                        ->where('id', $device->id)
                        ->update(['token_expires_at' => $expiresAt]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('sync_devices', function (Blueprint $table): void {
            $table->dropIndex('sync_devices_token_expiry_index');
            $table->dropColumn('token_expires_at');
        });
    }
};
