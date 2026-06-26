<?php

namespace App\Services;

use App\Helpers\Fingerprint;
use App\Helpers\LicenseVerifier;
use App\Helpers\LicenseKeyParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LicenseChecker
{
    public function isValid(): bool
    {
        $license = DB::table('licenses')->latest()->first();
        if (!$license) return false;

        if (!LicenseVerifier::verify($license->payload_json, $license->signature)) {
            $this->log('invalid-signature');
            return false;
        }

        $payload = json_decode($license->payload_json, true);

        $runtimeFingerprint = Fingerprint::compute('middleware-validation');

        if (!hash_equals($runtimeFingerprint, (string) ($payload['fingerprint'] ?? ''))) {
            $this->log('fingerprint-mismatch');
            return false;
        }

        $now = Carbon::now();
        if ($this->clockRolledBack($now)) {
            $this->log('clock-rollback');
            return false;
        }

        if (isset($payload['start_date']) && $now->lt(Carbon::parse($payload['start_date']))) {
            $this->log('not-yet-valid');
            return false;
        }

        if (isset($payload['expiry_date']) && $now->gt(Carbon::parse($payload['expiry_date']))) {
            $this->log('expired');
            return false;
        }

        $this->storeLastSeen($now);
        $this->log('valid');
        return true;
    }

    public function checkConcurrentUsers(): bool
    {
        $license = DB::table('licenses')->latest()->first();
        if (!$license) return true; 

        $payload = json_decode($license->payload_json, true);
        
        $limit = (int) ($payload['max_concurrent_users'] ?? 0);
        if ($limit === 0) return true; // 0 = Unlimited

        // Count active distinct users in the sessions table
        // We consider a session active if last_activity is within the lifetime
        $lifetime = config('session.lifetime', 120) * 60; // in seconds
        $threshold = now()->getTimestamp() - $lifetime;

        $activeCount = DB::table('sessions')
            ->where('last_activity', '>', $threshold)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');
            
        // Check if current user is already counted
        $isCurrentUserActive = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('last_activity', '>', $threshold)
            ->exists();

        if (!$isCurrentUserActive && $activeCount >= $limit) {
            return false;
        }

        return true;
    }

    public function applyKey(string $key): void
    {
        $data = LicenseKeyParser::parse($key);

        if (!LicenseVerifier::verify($data['payload_json'], $data['signature'])) {
            throw new \RuntimeException('Invalid license signature');
        }

        if (!hash_equals(
            Fingerprint::compute('license-application'),
            $data['payload']['fingerprint']
        )) {
            throw new \RuntimeException('Fingerprint mismatch. This license is not for this server.');
        }

        if (
            isset($data['payload']['expiry_date']) &&
            Carbon::parse($data['payload']['expiry_date'])->isPast()
        ) {
            throw new \RuntimeException('License has already expired');
        }

        DB::transaction(function () use ($data) {
            DB::table('licenses')->update(['valid' => false]);

            DB::table('licenses')->insert([
                'customer_id'  => $data['payload']['customer_id'] ?? null,
                'payload_json' => $data['payload_json'],
                'signature'    => $data['signature'],
                'fingerprint'  => $data['payload']['fingerprint'],
                'start_date'   => $data['payload']['start_date'] ?? now(),
                'expiry_date'  => $data['payload']['expiry_date'] ?? null,
                'valid'        => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });

        $this->storeLastSeen(now());
        $this->log('license-key-applied');
    }

    private function storeLastSeen(Carbon $time): void
    {
        Storage::put(
            'license_last_seen.enc',
            Crypt::encryptString($time->toIso8601String())
        );
    }

    private function clockRolledBack(Carbon $now): bool
    {
        if (!Storage::exists('license_last_seen.enc')) return false;

        try {
            $last = Carbon::parse(
                Crypt::decryptString(Storage::get('license_last_seen.enc'))
            );
            return $now->lt($last->copy()->subMinutes(5));
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function log(string $status): void
    {
        DB::table('license_checks')->insert([
            'status' => $status,
            'checked_at' => now()
        ]);
    }
}
