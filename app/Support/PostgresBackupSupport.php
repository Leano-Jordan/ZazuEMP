<?php

namespace App\\Support;

use Illuminate\\Support\\Facades\\File;
use Symfony\\Component\\Process\\Process;

class PostgresBackupSupport
{
    public function dumpToFile(string $path): void
    {
        $connection = $this->connection();
        $process = new Process([
            'pg_dump',
            '--no-owner',
            '--no-privileges',
            '--clean',
            '--if-exists',
            '--format=plain',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--dbname='.$connection['database'],
        ], null, $connection['environment']);

        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful() || trim($process->getOutput()) === '') {
            throw new \\RuntimeException('PostgreSQL dump failed or returned empty SQL: '.$process->getErrorOutput());
        }

        if (File::put($path, $process->getOutput()) === false || !is_file($path) || filesize($path) === 0) {
            throw new \\RuntimeException('Could not write the PostgreSQL database snapshot.');
        }
    }

    public function restoreFromFile(string $path): void
    {
        if (!is_file($path) || filesize($path) === 0) {
            throw new \\RuntimeException('PostgreSQL import dump is empty or unavailable.');
        }

        $connection = $this->connection();
        $process = new Process([
            'psql',
            '--set=ON_ERROR_STOP=1',
            '--single-transaction',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--dbname='.$connection['database'],
        ], null, $connection['environment']);

        $process->setTimeout(600);
        $process->setInput(File::get($path));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \\RuntimeException('PostgreSQL import failed: '.$process->getErrorOutput());
        }
    }

    /**
     * @return array{host:string,port:string,username:string,database:string,environment:array<string,string>}
     */
    private function connection(): array
    {
        $config = config('database.connections.pgsql');
        $url = $config['url'] ?? null;

        if (is_string($url) && $url !== '') {
            $parts = parse_url($url);
            if (!is_array($parts) || !isset($parts['host'], $parts['path'])) {
                throw new \\RuntimeException('The PostgreSQL DB_URL is invalid for backup or restore.');
            }

            parse_str($parts['query'] ?? '', $query);
            $config = array_merge($config, [
                'host' => $parts['host'],
                'port' => $parts['port'] ?? 5432,
                'username' => isset($parts['user']) ? rawurldecode($parts['user']) : '',
                'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : '',
                'database' => ltrim($parts['path'], '/'),
                'sslmode' => $query['sslmode'] ?? ($config['sslmode'] ?? 'prefer'),
            ]);
        }

        foreach (['host', 'port', 'username', 'database'] as $key) {
            if (!isset($config[$key]) || (string) $config[$key] === '') {
                throw new \\RuntimeException('PostgreSQL connection configuration is missing '.$key.'.');
            }
        }

        return [
            'host' => (string) $config['host'],
            'port' => (string) $config['port'],
            'username' => (string) $config['username'],
            'database' => (string) $config['database'],
            'environment' => array_filter([
                'PGPASSWORD' => $config['password'] ?? null,
                'PGSSLMODE' => $config['sslmode'] ?? null,
            ], static fn ($value) => $value !== null && $value !== ''),
        ];
    }
}
