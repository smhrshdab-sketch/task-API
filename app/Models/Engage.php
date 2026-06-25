<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engage extends Model
{
    /** @use HasFactory<\Database\Factories\EngageFactory> */
    use HasFactory;
    protected $fillable = [
        'contributed_by',
        'contributor',
        'task',
        'description',
        'status'
    ];
    //=================================================
    public function task(){
        return $this->belongsTo(Task::class);
    }
    public function membership(){
        return $this->belongsTo(Membership::class,'contributor');
    }
}
