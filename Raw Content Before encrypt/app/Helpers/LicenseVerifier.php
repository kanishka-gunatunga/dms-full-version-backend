<?php

namespace App\Helpers;

class LicenseVerifier
{
    public static function verify(string $payloadJson, string $signature): bool
    {
        $publicKeyPath = base_path(env('LICENSE_PUBLIC_KEY_PATH', 'storage/keys/license_public.pem'));
        
        if (!file_exists($publicKeyPath)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public(
            file_get_contents($publicKeyPath)
        );

        if (!$publicKey) {
            return false;
        }

        return openssl_verify(
            $payloadJson,
            base64_decode($signature, true),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;
    }
}
