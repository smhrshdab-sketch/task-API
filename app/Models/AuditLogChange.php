<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogChange extends Model
{
    //
    protected $fillable = [
        'audit_log_id',
        'field_name',
        'old_value',
        'new_value',
        'value_type'
    ];
    //==============
    public function auditLog(){
        return $this->belongsTo(AuditLog::class);
    }
}
