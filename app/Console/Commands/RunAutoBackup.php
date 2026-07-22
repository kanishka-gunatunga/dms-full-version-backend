<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunAutoBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger an automated system backup based on .env configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $destination = env('AUTO_BACKUP_DESTINATION', 'local'); // 'local', 'ftp', 'google_drive'
        
        $destinationConfig = null;
        
        if ($destination === 'ftp') {
            $destinationConfig = [
                'host' => env('AUTO_BACKUP_FTP_HOST'),
                'username' => env('AUTO_BACKUP_FTP_USERNAME'),
                'password' => env('AUTO_BACKUP_FTP_PASSWORD'),
                'port' => env('AUTO_BACKUP_FTP_PORT', 21),
                'path' => env('AUTO_BACKUP_FTP_PATH', '/'),
            ];
        } elseif ($destination === 'google_drive') {
            $destinationConfig = [
                'client_id' => env('AUTO_BACKUP_GD_CLIENT_ID'),
                'client_secret' => env('AUTO_BACKUP_GD_CLIENT_SECRET'),
                'refresh_token' => env('AUTO_BACKUP_GD_REFRESH_TOKEN'),
                'folder_id' => env('AUTO_BACKUP_GD_FOLDER_ID'),
            ];
        }

        $backup = \App\Models\SystemBackup::create([
            'status' => 'pending',
            'destination' => $destination,
            'destination_config' => $destinationConfig,
            'is_auto' => true,
        ]);

        \App\Jobs\GenerateSystemBackupJob::dispatch($backup);

        $this->info("Automated backup dispatched. Backup ID: {$backup->id}");
    }
}
