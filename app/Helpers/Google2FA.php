<?php

namespace App\Helpers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
class Google2FA
{
    /**
     * Generate Base32 Secret
     */
    public static function generateSecretKey($length = 16)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Generate QR Code (BASE64 SVG)
     */
    public static function generateQRCode($email, $secret, $issuer = 'DMS-CMG')
{
    $email = rawurlencode($email);
    $issuer = rawurlencode($issuer);

    $otpauth = "otpauth://totp/{$issuer}:{$email}?secret={$secret}&issuer={$issuer}";

   $result = new Builder(
    writer: new PngWriter(),
    data: $otpauth,
    size: 250,
    margin: 10
);

   $qrCode = $result->build();

return 'data:image/png;base64,' . base64_encode($qrCode->getString());
}

    /**
     * Verify OTP Code
     */
    public static function verifyKey($secret, $code, $discrepancy = 1)
    {
        if (!preg_match('/^[0-9]{6}$/', $code)) {
            return false;
        }

        $timeSlice = floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {

            if (self::getCode($secret, $timeSlice + $i) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP Code
     */
    private static function getCode($secret, $timeSlice)
    {
        $key = self::base32Decode($secret);

        $time = pack('N*', 0) . pack('N*', $timeSlice);

        $hash = hash_hmac('sha1', $time, $key, true);

        $offset = ord(substr($hash, -1)) & 0x0F;

        $binary = substr($hash, $offset, 4);

        $value = unpack('N', $binary)[1] & 0x7FFFFFFF;

        return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Base32 decode
     */
    private static function base32Decode($secret)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        $secret = strtoupper($secret);
        $binary = '';

        for ($i = 0; $i < strlen($secret); $i++) {

            $pos = strpos($alphabet, $secret[$i]);

            if ($pos === false) {
                continue;
            }

            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binary, 8);

        $output = '';

        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $output .= chr(bindec($chunk));
            }
        }

        return $output;
    }
}