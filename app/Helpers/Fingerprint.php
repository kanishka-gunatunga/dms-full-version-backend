<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fingerprint
{
    /**
     * Generate a stable server fingerprint
     *
     * @param string $context
     * @return string
     */
    public static function compute(string $context = 'runtime'): string
    {
        $type = env('LICENSE_FINGERPRINT_TYPE', 'hardware');

        if ($type === 'file') {
            $identity = self::getOrCreateFileId();
            $source = 'file_persistent_uuid';
        } else {
            $identity = self::getHardwareId();
            $source = 'hardware_id';
        }

        // Harden fingerprint using APP_KEY (prevents cloning)
        $salted = $identity . '|' . config('app.key');
        $fingerprint = 'sha256:' . hash('sha256', $salted);

        Log::channel('fingerprint')->info('Fingerprint computed', [
            'context'     => $context,
            'source'      => $source,
            'fingerprint' => $fingerprint,
        ]);

        return $fingerprint;
    }

    /**
     * Get unique hardware identity
     */
    private static function getHardwareId(): string
    {
        $parts = [];

        // 1. Disk Identification
        if (PHP_OS_FAMILY === 'Windows') {
            $disk = shell_exec('vol C:');
            preg_match('/Serial Number is ([\w-]+)/', $disk ?? '', $m);
            if (isset($m[1])) $parts[] = $m[1];
        } else {
            $disk = shell_exec("lsblk -o UUID -dn | head -n1");
            if ($disk) $parts[] = trim($disk);
        }

        // 2. Network MAC Address
        $mac = null;
        if (PHP_OS_FAMILY === 'Windows') {
            $macs = explode("\n", shell_exec('getmac') ?: '');
            foreach ($macs as $line) {
                if (preg_match('/^([0-9A-F]{2}(-[0-9A-F]{2}){5})/i', trim($line), $m)) {
                    $mac = $m[1];
                    break;
                }
            }
        } else {
            $macs = explode("\n", shell_exec("cat /sys/class/net/*/address") ?: '');
            foreach ($macs as $m) {
                $m = trim($m);
                if ($m && $m !== '00:00:00:00:00:00') {
                    $mac = $m;
                    break;
                }
            }
        }
        if ($mac) $parts[] = $mac;

        // 3. CPU Model
        if (PHP_OS_FAMILY === 'Windows') {
            $cpu = shell_exec('powershell -command "Get-CimInstance Win32_Processor | Select-Object -ExpandProperty Name"');
            // Clean output (remove extra spaces/newlines)
            $cpuModel = trim(preg_replace('/\s+/', ' ', $cpu ?: ''));
        } else {
            $cpu = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | head -n1") ?: '');
            $cpuModel = preg_replace('/model name\s*:\s*/i', '', $cpu);
        }
        if ($cpuModel) $parts[] = trim($cpuModel);

        return implode('|', $parts) ?: 'fallback-hardware-id';
    }

    /**
     * Get or create a persistent file-based UUID
     */
    private static function getOrCreateFileId(): string
    {
        $file = 'fingerprint.lock';

        if (Storage::disk('local')->exists($file)) {
            return trim(Storage::disk('local')->get($file));
        }

        $uuid = 'server:' . Str::uuid()->toString();
        Storage::disk('local')->put($file, $uuid);

        return $uuid;
    }
}
