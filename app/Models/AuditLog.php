<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'event',
        'description',
        'batch_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'metadata',
        'causer_id',
        'created_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime'
    ];

    //=================
    public function causer(){
        return $this->belongsTo(Membership::class,'causer_id', 'id');
    }
    public function changes(){
        return $this->hasMany(AuditLogChange::class,'audit_log_id');
    }
}
