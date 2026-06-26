<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documents extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'type',
        'category',
        'sector_category',
        'storage',
        'description',
        'meta_tags',
        'document_preview',
        'file_path',
        'is_archived',
        'is_indexed',
        'uploaded_method',
        'attributes',
        'expiration_date',
        'indexed_or_encrypted',
        'signed_locked_by',
        'signed_lock_expires_at',
    ];

    public function sharedRoles()
    {
        return $this->hasMany(DocumentSharedRoles::class, 'document_id');
    }

    public function sharedUsers()
    {
        return $this->hasMany(DocumentSharedUsers::class, 'document_id');
    }
    public function sector()
    {
        return $this->belongsTo(Sectors::class, 'sector_category', 'id');
    }
    public function category_data()
    {
        return $this->belongsTo(Categories::class, 'category', 'id');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category', 'id');
    }

    public function auditTrails()
    {
        return $this->hasMany(DocumentAuditTrial::class, 'document');
    }
     public function signatures()
    {
        return $this->hasMany(DocumentSignature::class, 'document_id');
    }
    
}
