<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Fingerprint;

class LicenseFingerprint extends Command
{
    protected $signature = 'license:fingerprint';
    protected $description = 'Show server fingerprint';

    public function handle()
    {
        $this->info("Current Fingerprint Type: " . env('LICENSE_FINGERPRINT_TYPE', 'hardware'));
        $this->info("Fingerprint: " . Fingerprint::compute('cli-check'));
    }
}
