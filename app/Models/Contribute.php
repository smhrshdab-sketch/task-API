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
    public function department(){
        //logger('IN => Contribute_Model');
        $rel = $this->belongsTo(Department::class,'department');
        //logger('==> department: ',[$rel]);
        return $rel;
    }
    public function task(){
        return $this->belongsTo(Membership::class,'task');
    }
}
