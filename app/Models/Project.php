<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjecFactory> */
    use HasFactory;
    //================
    public function department(){
        return $this->belongsTo(Department::class);
    }
    //==========
    public function tasks(){
        return $this->hasMany(Task::class);
    }
    //==============
    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

}
