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
        logger('IN => Engage_Model');
        $rel = $this->belongsTo(Task::class);
        logger('==>  task: ',[$rel]);
        return $rel;
    }
    public function membership(){
        logger('IN => Engage_Model');
        $rel = $this->belongsTo(Membership::class, 'contributor');
        //return $this->belongsTo(Membership::class, 'contributor');
        logger('==> membership: ',[$rel]);
        return $rel;
    }
}
