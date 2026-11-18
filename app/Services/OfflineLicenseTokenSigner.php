<?php

namespace App\Services;

final class OfflineLicenseTokenSigner
{
    public function sign(array $payload, string $keyFile): string
    {
        $this->assertSodiumAvailable();
        $secretKey = $this->loadSecretKey($keyFile);
        $payloadBytes = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $signature = sodium_crypto_sign_detached($payloadBytes, $secretKey);
        $token = $this->base64UrlEncode($payloadBytes).'.'.$this->base64UrlEncode($signature);

        if (strlen($token) > 16384) {
            throw new \InvalidArgumentException('The signed licence exceeds the supported token size.');
        }

        return $token;
    }

    private function assertSodiumAvailable(): void
    {
        if (! function_exists('sodium_crypto_sign_detached')) {
            throw new \InvalidArgumentException('PHP Sodium is required to issue signed Zazu licences.');
        }
    }

    private function loadSecretKey(string $keyFile): string
    {
        $realPath = realpath(trim($keyFile));
        if ($realPath === false || ! is_file($realPath) || ! is_readable($realPath)) {
            throw new \InvalidArgumentException('A readable external signing-key file is required.');
        }

        $projectRoot = realpath(base_path()) ?: base_path();
        $projectPrefix = strtolower(rtrim(str_replace('\\', '/', $projectRoot), '/').'/');
        $keyPath = strtolower(str_replace('\\', '/', $realPath));
        if (str_starts_with($keyPath, $projectPrefix)) {
            throw new \InvalidArgumentException('The signing key must be stored outside the Zazu project.');
        }

        $encodedKey = file_get_contents($realPath);
        $secretKey = is_string($encodedKey) ? base64_decode(trim($encodedKey), true) : false;
        if ($secretKey === false || strlen($secretKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new \InvalidArgumentException('The external signing-key file is not a valid base64 Ed25519 secret key.');
        }

        $publicKey = base64_decode((string) config('zazu.licensing.public_key', ''), true);
        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new \InvalidArgumentException('The matching ZAZU_LICENSE_PUBLIC_KEY is not configured.');
        }

        if (! hash_equals($publicKey, sodium_crypto_sign_publickey_from_secretkey($secretKey))) {
            throw new \InvalidArgumentException('The external signing key does not match the configured Zazu public key.');
        }

        return $secretKey;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
