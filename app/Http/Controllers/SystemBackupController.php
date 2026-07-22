<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemBackup;
use App\Jobs\GenerateSystemBackupJob;
use Illuminate\Support\Facades\Storage;

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
}
