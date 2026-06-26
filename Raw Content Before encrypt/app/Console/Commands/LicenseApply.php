<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseChecker;

class LicenseApply extends Command
{
    protected $signature = 'license:apply {key}';
    protected $description = 'Apply a license key';

    public function handle()
    {
        $key = $this->argument('key');
        
        try {
            app(LicenseChecker::class)->applyKey($key);
            $this->info('License applied successfully');
        } catch (\Throwable $e) {
            $this->error('Failed to apply license: ' . $e->getMessage());
        }
    }
}
