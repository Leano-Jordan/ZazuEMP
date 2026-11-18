<?php

namespace App\Console\Commands;

use App\Support\ZazuLocalSentinelRecovery;
use Illuminate\Console\Command;
use Throwable;

final class ZazuSentinelRecoveryCode extends Command
{
    protected $signature = 'zazu:sentinel-recovery-code';

    protected $description = 'Generate a one-use local Sentinel recovery code valid for five minutes';

    public function handle(ZazuLocalSentinelRecovery $recovery): int
    {
        try {
            $code = $recovery->issue();
        } catch (Throwable $exception) {
            $this->error('Sentinel could not create a local recovery code. Check the installation storage permissions.');

            report($exception);

            return self::FAILURE;
        }

        $this->newLine();
        $this->warn('LOCAL SENTINEL RECOVERY CODE — valid for five minutes, one use only');
        $this->line($code);
        $this->newLine();
        $this->comment('Open http://127.0.0.1:8000/api/sentinel/recovery on this PC and enter the code.');
        $this->comment('Do not send this code to anyone. Generating another code invalidates this one.');

        return self::SUCCESS;
    }
}
