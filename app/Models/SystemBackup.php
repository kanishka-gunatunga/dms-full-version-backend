<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'status',
        'file_size',
        'destination',
        'destination_config',
        'error_message',
        'is_auto',
    ];

    protected $casts = [
        'destination_config' => 'array',
    ];
}
