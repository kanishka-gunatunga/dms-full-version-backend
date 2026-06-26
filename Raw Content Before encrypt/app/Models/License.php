<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';

    protected $fillable = [
        'customer_id',
        'payload_json',
        'signature',
        'fingerprint',
        'start_date',
        'expiry_date',
        'last_validated_at',
        'valid',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expiry_date' => 'datetime',
        'last_validated_at' => 'datetime',
        'valid' => 'boolean',
    ];

    /**
     * Decode payload JSON automatically
     */
    public function getPayloadAttribute(): array
    {
        return json_decode($this->payload_json, true) ?? [];
    }

    /**
     * License check logs
     */
    public function checks()
    {
        return $this->hasMany(LicenseCheck::class);
    }
}
