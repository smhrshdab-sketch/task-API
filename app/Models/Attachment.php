<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'original_name',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'size',
        'uploaded_by',
        'is_public',
    ];
    
    public function attachable(){
        return $this->morphTo();
    }
    
    // Uploader is now a Membership, not Account
    public function uploader(){
        return $this->belongsTo(Membership::class, 'uploaded_by');
    }
    
    // Helper to get the actual account through membership
    public function getUploaderAccountAttribute(){
        return $this->uploader->account;
    }
}
