<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemBackup;
use App\Models\Documents;
use App\Models\FTPAccounts;
use App\Jobs\GenerateSystemBackupJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Exception;

class SystemBackupController extends Controller
{
    public function index()
    {
        $backups = SystemBackup::orderBy('created_at', 'desc')->get();
        return response()->json(['backups' => $backups]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'destination' => 'required|in:local,ftp,google_drive',
        ]);

        $destination = $request->destination;
        $destinationConfig = null;

        if ($destination === 'ftp') {
            $destinationConfig = [
                'host' => env('AUTO_BACKUP_FTP_HOST'), 
                'username' => env('AUTO_BACKUP_FTP_USERNAME'),
                'password' => env('AUTO_BACKUP_FTP_PASSWORD'),
                'port' => env('AUTO_BACKUP_FTP_PORT', 21),
                'path' => env('AUTO_BACKUP_FTP_PATH', '/'),
            ];
            
            if (!$destinationConfig['host'] || !$destinationConfig['username']) {
                $backup = SystemBackup::create([
                    'status' => 'failed',
                    'destination' => $destination,
                    'error_message' => 'FTP configuration is missing in .env file',
                    'is_auto' => false,
                ]);
                return response()->json(['message' => 'Configuration error', 'backup' => $backup]);
            }
        } elseif ($destination === 'google_drive') {
            $destinationConfig = [
                'client_id' => env('AUTO_BACKUP_GD_CLIENT_ID'),
                'client_secret' => env('AUTO_BACKUP_GD_CLIENT_SECRET'),
                'refresh_token' => env('AUTO_BACKUP_GD_REFRESH_TOKEN'),
                'folder_id' => env('AUTO_BACKUP_GD_FOLDER_ID'),
            ];

            if (!$destinationConfig['client_id'] || !$destinationConfig['refresh_token']) {
                $backup = SystemBackup::create([
                    'status' => 'failed',
                    'destination' => $destination,
                    'error_message' => 'Google Drive configuration is missing in .env file',
                    'is_auto' => false,
                ]);
                return response()->json(['message' => 'Configuration error', 'backup' => $backup]);
            }
        }

        $backup = SystemBackup::create([
            'filename' => 'pending',
            'status' => 'pending',
            'destination' => $destination,
            'destination_config' => $destinationConfig,
            'is_auto' => false,
        ]);

        GenerateSystemBackupJob::dispatch($backup);

        return response()->json(['message' => 'Backup generation started', 'backup' => $backup]);
    }

    public function download($id)
    {
        $backup = SystemBackup::findOrFail($id);
        
        if ($backup->status !== 'completed') {
            return response()->json(['error' => 'Backup is not completed'], 400);
        }

        if ($backup->destination !== 'local') {
            return response()->json(['error' => 'Only local backups can be downloaded directly'], 400);
        }

        if (!Storage::disk('local')->exists($backup->filename)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $tempUrl = Storage::disk('local')->temporaryUrl(
            $backup->filename,
            now()->addHour()
        );

        return response()->json([
            'status' => 'success',
            'data' => $tempUrl,
        ]);
    }

    public function restore($id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $backup = SystemBackup::findOrFail($id);

        if ($backup->status !== 'completed') {
            return response()->json(['error' => 'Only completed backups can be restored'], 400);
        }

        try {
            $tempDir = storage_path("app/private/backups/restore_temp_{$backup->id}_" . time());
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zipFilePath = $tempDir . '/backup.zip';

            // 1. Fetch the zip file
            if ($backup->destination === 'local') {
                if (!Storage::disk('local')->exists($backup->filename)) {
                    throw new Exception('Backup file not found on local storage');
                }
                copy(storage_path('app/private/' . $backup->filename), $zipFilePath);
            } elseif ($backup->destination === 'ftp') {
                $config = $backup->destination_config;
                if (!$config) throw new Exception('FTP configuration missing for this backup');
                
                config(["filesystems.disks.restore_ftp_src" => [
                    'driver' => 'ftp',
                    'host' => $config['host'],
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'port' => (int) ($config['port'] ?? 21),
                ]]);
                
                if (!Storage::disk('restore_ftp_src')->exists($backup->filename)) {
                    throw new Exception('Backup file not found on FTP server');
                }
                
                $stream = Storage::disk('restore_ftp_src')->readStream($backup->filename);
                file_put_contents($zipFilePath, stream_get_contents($stream));
                if (is_resource($stream)) fclose($stream);

            } elseif ($backup->destination === 'google_drive') {
                $config = $backup->destination_config;
                if (!$config) throw new Exception('Google Drive configuration missing for this backup');
                
                config(["filesystems.disks.restore_gdrive_src" => [
                    'driver' => 'google',
                    'clientId' => $config['client_id'],
                    'clientSecret' => $config['client_secret'],
                    'refreshToken' => $config['refresh_token'],
                    'folderId' => $config['folder_id'] ?? null,
                ]]);
                
                if (!Storage::disk('restore_gdrive_src')->exists($backup->filename)) {
                    throw new Exception('Backup file not found on Google Drive');
                }

                $stream = Storage::disk('restore_gdrive_src')->readStream($backup->filename);
                file_put_contents($zipFilePath, stream_get_contents($stream));
                if (is_resource($stream)) fclose($stream);
            }

            // 2. Extract the zip file
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new Exception('Failed to extract the backup zip file');
            }

            // 3. Restore Database
            $sqlFiles = glob($tempDir . '/*.sql');
            if (empty($sqlFiles)) {
                throw new Exception('No SQL dump file found in the backup');
            }
            $sqlFile = $sqlFiles[0];
            
            DB::unprepared(file_get_contents($sqlFile));

            // Re-fetch FTP accounts to use for documents
            $ftpAccounts = FTPAccounts::all()->keyBy('id');

            // 4. Restore Documents
            $documentsDir = $tempDir . '/documents';
            if (is_dir($documentsDir)) {
                $documents = Documents::all();
                
                foreach ($documents as $doc) {
                    $filePath = $doc->file_path;
                    if (empty($filePath)) continue;

                    $extractedFilePath = $documentsDir . '/' . basename($filePath);
                    if (!file_exists($extractedFilePath)) continue;

                    $storageType = $doc->storage;

                    if ($storageType === 'local' || $storageType === 'Local Disk (Default)' || empty($storageType)) {
                        $localDestPath = storage_path('app/private/' . $filePath);
                        $dir = dirname($localDestPath);
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        copy($extractedFilePath, $localDestPath);
                    } else {
                        $ftpAccount = $ftpAccounts->get($storageType);
                        if ($ftpAccount) {
                            $diskName = 'restore_dynamic_ftp_' . $ftpAccount->id;
                            config(["filesystems.disks.{$diskName}" => [
                                'driver' => 'ftp',
                                'host' => $ftpAccount->host,
                                'username' => $ftpAccount->username,
                                'password' => $ftpAccount->password,
                                'port' => (int) ($ftpAccount->port ?? 21),
                            ]]);
                            
                            $stream = fopen($extractedFilePath, 'r');
                            Storage::disk($diskName)->put($filePath, $stream);
                            if (is_resource($stream)) fclose($stream);
                        }
                    }
                }
            }

            // 5. Cleanup
            File::deleteDirectory($tempDir);

            return response()->json(['message' => 'System restored successfully']);

        } catch (Exception $e) {
            if (isset($tempDir) && is_dir($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return response()->json(['error' => 'Restore failed: ' . $e->getMessage()], 500);
        }
    }
}
