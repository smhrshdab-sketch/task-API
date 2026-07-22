<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Task;

class EngageService{
    public function engaging(array $data, Membership $membership,Task $task){
        logger('Data,Membership,Task',[$data,$membership,$task]);
        return DB::transaction(function () use ($data, $membership) {
            $description = $data['description'] ?? null;
        });
    }
}