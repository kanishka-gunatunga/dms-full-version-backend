<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseChecker;
use Illuminate\Support\Facades\DB;

class LicenseStatus extends Command
{
    protected $signature = 'license:status';
    protected $description = 'Check current license status';

    public function handle()
    {
        $isValid = app(LicenseChecker::class)->isValid();
        
        if ($isValid) {
            $this->info('License is VALID');
            
            $license = DB::table('licenses')->where('valid', true)->latest()->first();
            if ($license) {
                $this->line('Customer ID: ' . $license->customer_id);
                $this->line('Expiry Date: ' . ($license->expiry_date ?: 'Never'));
            }
        } else {
            $this->error('License is INVALID or MISSING');
            
            $lastCheck = DB::table('license_checks')->latest()->first();
            if ($lastCheck) {
                $this->line('Last failure reason: ' . $lastCheck->status);
            }
        }
    }
}
