<?php

namespace App\Jobs;

use App\Models\SystemBackup;
use App\Models\Documents;
use App\Models\FTPAccounts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Ifsnop\Mysqldump\Mysqldump;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Log;

class GenerateSystemBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0; // No timeout for this job
    protected $systemBackup;

    /**
     * Create a new job instance.
     */
    public function __construct(SystemBackup $systemBackup)
    {
        $this->systemBackup = $systemBackup;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $backupId = $this->systemBackup->id;
            $timestamp = date('Ymd_His');
            $tempDir = storage_path("app/private/backups/temp_{$backupId}");
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // 1. Dump MySQL Database
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST', '127.0.0.1');
            $sqlFilePath = $tempDir . "/database_{$timestamp}.sql";

            try {
                $dump = new Mysqldump("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
                $dump->start($sqlFilePath);
            } catch (Exception $e) {
                throw new Exception("Database dump failed: " . $e->getMessage());
            }

            // 2. Initialize Zip Archive
            $zipFileName = "backup_{$timestamp}.zip";
            $zipFilePath = $tempDir . "/{$zipFileName}";
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Failed to create ZIP file at {$zipFilePath}");
            }

            // Add DB dump to zip
            $zip->addFile($sqlFilePath, basename($sqlFilePath));

            // 3. Process Documents
            $documents = Documents::all();
            
            // Cache FTP configurations
            $ftpAccounts = FTPAccounts::all()->keyBy('id');

            foreach ($documents as $doc) {
                $filePath = $doc->file_path;
                if (empty($filePath)) continue;

                $storageType = $doc->storage;

                if ($storageType === 'local' || $storageType === 'Local Disk (Default)' || empty($storageType)) {
                    // It's local
                    $localPath = storage_path('app/private/' . $filePath); // Assuming local disk
                    if (!file_exists($localPath)) {
                         $localPath = storage_path('app/public/' . $filePath); // fallback public
                    }
                    if (file_exists($localPath)) {
                        $zip->addFile($localPath, 'documents/' . basename($filePath));
                    }
                } else {
                    // It's on an FTP
                    $ftpAccount = $ftpAccounts->get($storageType);
                    if ($ftpAccount) {
                        // Dynamically configure FTP disk
                        $diskName = 'dynamic_ftp_backup_' . $ftpAccount->id;
                        config(["filesystems.disks.{$diskName}" => [
                            'driver' => 'ftp',
                            'host' => $ftpAccount->host,
                            'username' => $ftpAccount->username,
                            'password' => $ftpAccount->password,
                            'port' => (int) ($ftpAccount->port ?? 21),
                        ]]);
                        
                        try {
                            if (Storage::disk($diskName)->exists($filePath)) {
                                $stream = Storage::disk($diskName)->readStream($filePath);
                                if ($stream) {
                                    $content = stream_get_contents($stream);
                                    if ($content !== false) {
                                        $zip->addFromString('documents/' . basename($filePath), $content);
                                    }
                                    fclose($stream);
                                }
                            }
                        } catch (Exception $e) {
                            Log::warning("Backup: Failed to fetch FTP file {$filePath} for document {$doc->id}");
                        }
                    }
                }
            }

            $zip->close();
            
            // Delete DB dump
            @unlink($sqlFilePath);

            // 4. Move Zip to destination
            $finalDisk = 'local';
            $finalPath = "backups/{$zipFileName}";
            
            if ($this->systemBackup->destination === 'ftp') {
                $config = $this->systemBackup->destination_config;
                if ($config) {
                     config(["filesystems.disks.custom_ftp_dest" => [
                        'driver' => 'ftp',
                        'host' => $config['host'],
                        'username' => $config['username'],
                        'password' => $config['password'],
                        'port' => (int) ($config['port'] ?? 21),
                    ]]);
                    $finalDisk = 'custom_ftp_dest';
                    $finalPath = ($config['path'] ?? '') . "/{$zipFileName}";
                }
            } elseif ($this->systemBackup->destination === 'google_drive') {
                 $config = $this->systemBackup->destination_config;
                 if ($config) {
                     config(["filesystems.disks.google_drive_dest" => [
                        'driver' => 'google',
                        'clientId' => $config['client_id'],
                        'clientSecret' => $config['client_secret'],
                        'refreshToken' => $config['refresh_token'],
                        'folderId' => $config['folder_id'] ?? null,
                     ]]);
                     $finalDisk = 'google_drive_dest';
                     $finalPath = $zipFileName;
                 }
            }

            // Stream to final destination
            $zipStream = fopen($zipFilePath, 'r');
            Storage::disk($finalDisk)->put($finalPath, $zipStream);
            try {
                if (is_resource($zipStream)) {
                    fclose($zipStream);
                }
            } catch (\Throwable $t) {
                // Flysystem V3 Google Drive adapter sometimes auto-closes the stream
            }
            
            $fileSize = filesize($zipFilePath);

            // Clean up zip
            @unlink($zipFilePath);
            @rmdir($tempDir);

            // Update record
            $this->systemBackup->update([
                'status' => 'completed',
                'filename' => $finalPath,
                'file_size' => $fileSize,
            ]);

        } catch (Exception $e) {
            Log::error("Backup Failed: " . $e->getMessage());
            $this->systemBackup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Backup Job Hard Failure: " . $exception->getMessage());
        if ($this->systemBackup) {
            $this->systemBackup->update([
                'status' => 'failed',
                'error_message' => substr($exception->getMessage(), 0, 500) // Ensure it fits in text column if length is limited
            ]);
        }
    }
}
