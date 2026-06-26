<?php

namespace App\Helpers;

class LicenseKeyParser
{
    public static function parse(string $key): array
    {
        if (!str_starts_with($key, 'LIC-v1-')) {
            throw new \Exception('Invalid license key format');
        }

        $body = substr($key, 7);

        if (!str_contains($body, '.')) {
            throw new \Exception('Malformed license key');
        }

        [$payloadB64, $signatureB64] = explode('.', $body, 2);

        $payloadJson = base64_decode($payloadB64, true);
        if ($payloadJson === false) {
            throw new \Exception('Invalid base64 payload');
        }

        return [
            'payload_json' => $payloadJson,
            'payload'      => json_decode($payloadJson, true),
            'signature'    => $signatureB64
        ];
    }
}
