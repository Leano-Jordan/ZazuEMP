<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 32)->nullable()->after('name');
        });

        foreach (DB::table('users')->select('id', 'name')->orderBy('id')->get() as $user) {
            $base = Str::lower(Str::ascii((string) $user->name));
            $base = preg_replace('/[^a-z0-9]+/', '.', $base) ?? '';
            $base = trim($base, '.');
            $base = substr($base ?: 'user', 0, 24);

            $candidate = $base;
            $suffix = 1;

            while (DB::table('users')->where('username', $candidate)->exists()) {
                $suffixText = (string) $suffix++;
                $candidate = substr($base, 0, max(1, 32 - strlen($suffixText) - 1)).'.'.$suffixText;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $candidate]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
