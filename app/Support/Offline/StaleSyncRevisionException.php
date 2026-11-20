<?php

namespace App\Support\Offline;

use RuntimeException;

final class StaleSyncRevisionException extends RuntimeException
{
    /**
     * @param array<string, mixed> $remotePayload
     */
    public function __construct(string $message, private readonly array $remotePayload)
    {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function remotePayload(): array
    {
        return $this->remotePayload;
    }
}
