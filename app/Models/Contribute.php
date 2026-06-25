<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribute extends Model
{
    /** @use HasFactory<\Database\Factories\ContributeFactory> */
    use HasFactory;
    protected $fillable = [
        'department',
        'task',
        'description',
        'status'
    ];
    //=================================================
    public function departmrnt(){
        return $this->belongsTo(Department::class);
    }
    public function membership(){
        return $this->belongsTo(Membership::class);
    }
}
