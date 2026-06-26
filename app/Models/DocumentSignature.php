<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'document_signatures';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'document_id',
        'user_id'
    ];

    public function document()
    {
        return $this->belongsTo(Documents::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(UserDetails::class, 'user_id', 'user_id'); // Or User::class depending on how you relate users
    }
}
