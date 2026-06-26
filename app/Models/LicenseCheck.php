<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenseCheck extends Model
{
    use HasFactory;

    protected $table = 'license_checks';

    public $timestamps = false;

    protected $fillable = [
        'status',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    /**
     * Optional: link to license (logical, not FK)
     */
    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
