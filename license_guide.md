# Master Guide: License System Integration

This guide provides everything needed to implement the License Issuing and Validation system in any Laravel 11+ project.

## 1. Database Schema
Run this SQL to create the tables. Do not use migrations if they are not allowed.

```sql
CREATE TABLE `licenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(255) DEFAULT NULL,
  `payload_json` text NOT NULL,
  `signature` text NOT NULL,
  `fingerprint` varchar(255) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `last_validated_at` datetime DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `license_checks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL,
  `checked_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 2. Environment Configuration ([.env](file:///d:/DMS_HNB/dms_backend_hnb/.env))
```env
# License Configuration
LICENSE_FINGERPRINT_TYPE=hardware  # Options: hardware (default), file
LICENSE_PUBLIC_KEY_PATH=storage/keys/license_public.pem
```

## 3. Models

### [app/Models/License.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Models/License.php)
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model {
    use HasFactory;
    protected $table = 'licenses';
    protected $fillable = ['customer_id', 'payload_json', 'signature', 'fingerprint', 'start_date', 'expiry_date', 'last_validated_at', 'valid'];
    protected $casts = ['start_date' => 'datetime', 'expiry_date' => 'datetime', 'last_validated_at' => 'datetime', 'valid' => 'boolean'];

    public function getPayloadAttribute(): array {
        return json_decode($this->payload_json, true) ?? [];
    }

    public function checks() {
        return $this->hasMany(LicenseCheck::class);
    }
}
```

### [app/Models/LicenseCheck.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Models/LicenseCheck.php)
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenseCheck extends Model {
    use HasFactory;
    protected $table = 'license_checks';
    public $timestamps = false;
    protected $fillable = ['status', 'checked_at'];
    protected $casts = ['checked_at' => 'datetime'];

    public function license() {
        return $this->belongsTo(License::class);
    }
}
```

## 4. Helpers

### [app/Helpers/Fingerprint.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Helpers/Fingerprint.php)
```php
<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Fingerprint {
    public static function compute(string $context = 'runtime'): string {
        $type = env('LICENSE_FINGERPRINT_TYPE', 'hardware');
        $identity = ($type === 'file') ? self::getOrCreateFileId() : self::getHardwareId();
        $salted = $identity . '|' . config('app.key');
        return 'sha256:' . hash('sha256', $salted);
    }

    private static function getHardwareId(): string {
        $parts = [];
        if (PHP_OS_FAMILY === 'Windows') {
            $disk = shell_exec('vol C:');
            preg_match('/Serial Number is ([\w-]+)/', $disk ?? '', $m);
            if (isset($m[1])) $parts[] = $m[1];
            $macs = explode("\n", shell_exec('getmac') ?: '');
            foreach ($macs as $line) if (preg_match('/^([0-9A-F]{2}(-[0-9A-F]{2}){5})/i', trim($line), $m)) { $parts[] = $m[1]; break; }
            $cpu = trim(shell_exec('wmic cpu get name') ?: '');
            $cpuLines = explode("\n", $cpu);
            $parts[] = trim($cpuLines[1] ?? $cpuLines[0] ?? '');
        } else {
            $disk = trim(shell_exec("lsblk -o UUID -dn | head -n1") ?: '');
            if ($disk) $parts[] = $disk;
            $mac = trim(shell_exec("cat /sys/class/net/*/address | head -n1") ?: '');
            if ($mac) $parts[] = $mac;
            $cpu = trim(shell_exec("cat /proc/cpuinfo | grep 'model name' | head -n1") ?: '');
            $parts[] = preg_replace('/model name\s*:\s*/i', '', $cpu);
        }
        return implode('|', $parts) ?: 'fallback-id';
    }

    private static function getOrCreateFileId(): string {
        $file = 'fingerprint.lock';
        if (Storage::disk('local')->exists($file)) return trim(Storage::disk('local')->get($file));
        $uuid = 'server:' . Str::uuid()->toString();
        Storage::disk('local')->put($file, $uuid);
        return $uuid;
    }
}
```

### [app/Helpers/LicenseVerifier.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Helpers/LicenseVerifier.php)
```php
<?php
namespace App\Helpers;
class LicenseVerifier {
    public static function verify(string $payloadJson, string $signature): bool {
        $publicKeyPath = base_path(env('LICENSE_PUBLIC_KEY_PATH', 'storage/keys/license_public.pem'));
        if (!file_exists($publicKeyPath)) return false;
        $publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));
        if (!$publicKey) return false;
        return openssl_verify($payloadJson, base64_decode($signature, true), $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }
}
```

### [app/Helpers/LicenseKeyParser.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Helpers/LicenseKeyParser.php)
```php
<?php
namespace App\Helpers;
class LicenseKeyParser {
    public static function parse(string $key): array {
        if (!str_starts_with($key, 'LIC-v1-')) throw new \Exception('Invalid format');
        $body = substr($key, 7);
        if (!str_contains($body, '.')) throw new \Exception('Malformed key');
        [$payloadB64, $signatureB64] = explode('.', $body, 2);
        $payloadJson = base64_decode($payloadB64, true);
        return ['payload_json' => $payloadJson, 'payload' => json_decode($payloadJson, true), 'signature' => $signatureB64];
    }
}
```

## 5. Service

### [app/Services/LicenseChecker.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Services/LicenseChecker.php)
```php
<?php
namespace App\Services;
use App\Helpers\{Fingerprint, LicenseVerifier, LicenseKeyParser};
use Carbon\Carbon;
use Illuminate\Support\Facades\{Crypt, DB, Storage};

class LicenseChecker {
    public function isValid(): bool {
        $license = DB::table('licenses')->latest()->first();
        if (!$license || !LicenseVerifier::verify($license->payload_json, $license->signature)) return false;
        $payload = json_decode($license->payload_json, true);
        if (!hash_equals(Fingerprint::compute(), (string)($payload['fingerprint'] ?? ''))) return false;
        $now = Carbon::now();
        if ($this->clockRolledBack($now)) return false;
        if (isset($payload['expiry_date']) && $now->gt(Carbon::parse($payload['expiry_date']))) return false;
        $this->storeLastSeen($now);
        return true;
    }

    public function checkConcurrentUsers(): bool {
        $license = DB::table('licenses')->latest()->first();
        if (!$license) return true; 
        $payload = json_decode($license->payload_json, true);
        
        $limit = (int) ($payload['max_concurrent_users'] ?? 0);
        if ($limit === 0) return true; // 0 = Unlimited

        $lifetime = config('session.lifetime', 120) * 60;
        $threshold = now()->getTimestamp() - $lifetime;

        $activeCount = DB::table('sessions')
            ->where('last_activity', '>', $threshold)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');
            
        $isCurrentUserActive = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('last_activity', '>', $threshold)
            ->exists();

        if (!$isCurrentUserActive && $activeCount >= $limit) {
            return false;
        }
        return true;
    }

    public function applyKey(string $key): void {
        $data = LicenseKeyParser::parse($key);
        if (!LicenseVerifier::verify($data['payload_json'], $data['signature'])) throw new \Exception('Invalid Signature');
        if (!hash_equals(Fingerprint::compute(), $data['payload']['fingerprint'])) throw new \Exception('Fingerprint Mismatch');
        DB::transaction(function() use ($data) {
            DB::table('licenses')->update(['valid' => false]);
            DB::table('licenses')->insert([
                'customer_id' => $data['payload']['customer_id'] ?? null,
                'payload_json' => $data['payload_json'],
                'signature' => $data['signature'],
                'fingerprint' => $data['payload']['fingerprint'],
                'expiry_date' => $data['payload']['expiry_date'] ?? null,
                'valid' => true, 'created_at' => now(), 'updated_at' => now()
            ]);
        });
    }

    private function storeLastSeen($time) { Storage::put('license_last_seen.enc', Crypt::encryptString($time->toIso8601String())); }
    private function clockRolledBack($now): bool {
        if (!Storage::exists('license_last_seen.enc')) return false;
        $last = Carbon::parse(Crypt::decryptString(Storage::get('license_last_seen.enc')));
        return $now->lt($last->copy()->subMinutes(5));
    }
}
```

## 6. Middleware & Controller

### [app/Http/Middleware/EnsureLicenseIsValid.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Http/Middleware/EnsureLicenseIsValid.php)
```php
<?php
namespace App\Http\Middleware;
use Closure;
use App\Services\LicenseChecker;

class EnsureLicenseIsValid {
    public function handle($request, Closure $next) {
        $allowed = ['api/license/apply', 'admin/license*'];
        foreach ($allowed as $route) if ($request->is($route)) return $next($request);
        
        $licenseChecker = app(LicenseChecker::class);
        if (!$licenseChecker->isValid()) {
            return response()->json(['error' => 'LICENSE_INVALID'], 403);
        }

        if (auth()->check() && !$licenseChecker->checkConcurrentUsers()) {
            return response()->json([
                'error' => 'CONCURRENT_USER_LIMIT',
                'message' => 'concurrent user limit reached try agin'
            ], 403);
        }

        return $next($request);
    }
}
```

### [app/Http/Controllers/LicenseController.php](file:///d:/DMS_HNB/dms_backend_hnb/app/Http/Controllers/LicenseController.php)
```php
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\LicenseChecker;

class LicenseController extends Controller {
    public function index() {
        return response()->json(['valid' => app(LicenseChecker::class)->isValid()]);
    }

    public function apply(Request $request) {
        $request->validate(['key' => 'required|string']);
        try {
            app(LicenseChecker::class)->applyKey($request->key);
            return response()->json(['success' => true, 'message' => 'License applied successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
```

### [bootstrap/app.php](file:///d:/DMS_HNB/dms-backend-v1-before-signature/bootstrap/app.php) (Registration)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\EnsureLicenseIsValid::class);
})
```

### [routes/api.php](file:///d:/DMS_HNB/dms_backend_hnb/routes/api.php) (Route Definition)
```php
use App\Http\Controllers\LicenseController;

Route::post('/license/apply', [LicenseController::class, 'apply']);
```

## 7. Console Commands

### `php artisan license:fingerprint`
```php
<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Helpers\Fingerprint;

class LicenseFingerprint extends Command {
    protected $signature = 'license:fingerprint';
    public function handle() { $this->info("Fingerprint: " . Fingerprint::compute()); }
}
```

### `php artisan license:status`
```php
<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\LicenseChecker;
use Illuminate\Support\Facades\DB;

class LicenseStatus extends Command {
    protected $signature = 'license:status';
    public function handle() {
        if (app(LicenseChecker::class)->isValid()) {
            $this->info('License is VALID');
            $license = DB::table('licenses')->where('valid', true)->latest()->first();
            if ($license) {
                $this->line('Customer ID: ' . $license->customer_id);
                $this->line('Expiry Date: ' . ($license->expiry_date ?: 'Never'));
            }
        } else {
            $this->error('License is INVALID or MISSING');
        }
    }
}
```

### `php artisan license:apply {key}`
```php
<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Services\LicenseChecker;

class LicenseApply extends Command {
    protected $signature = 'license:apply {key}';
    public function handle() {
        try {
            app(LicenseChecker::class)->applyKey($this->argument('key'));
            $this->info('License applied successfully');
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
        }
    }
}
```

## 8. Final Steps
1. Create `storage/keys` folder.
2. Put [license_public.pem](file:///d:/DMS_HNB/license_generator/license_public.pem) inside it.
3. Run `php artisan license:fingerprint` to get your server ID.
