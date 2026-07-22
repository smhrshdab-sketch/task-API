<?php
namespace App\Services;

use App\Models\Contribute;
use App\Models\Engage;
use App\Models\Membership;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class ContributeService{
    public function attachDepartment(Task $task,array $departmentIds,){
        //logger('🔵💥🔵ContributeService (attachDepartment) [task,departmentIds]: ',[$task,$departmentIds]);
        $managerRole = Role::where('slug','manager')->first();;
        //Log::info('managerRole and id: ', [$managerRole->slug,$managerRole->id]);
        if($departmentIds){
            foreach($departmentIds as $depId){
                $contribute = Contribute::create([
                    'department' => $depId,
                    'task' => $task->id
                ]);
                //Log::info('A contribute is created', [$contribute]);
                $manager = Membership::where('department_id',$depId)->where('role_id',$managerRole->id)->first();
                if (!$manager) {
                    Log::warning("No manager found for department: {$depId}");
                    continue;
                }
                else{                    
                    //Log::info('manager and id: ', [$manager,$manager->id]);
                    $managerEngaged = Engage::create([
                        'depId_by' => current_membership()->id,
                        'contributor' => $manager->id,
                        'task' => $task->id
                    ]);
                    //Log::info('A manager is engaged', [$managerEngaged]);
                }
            }
        } 
    }
}