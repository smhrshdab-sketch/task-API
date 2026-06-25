<?php

namespace Tests\Unit;

use Mockery;
use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\Account;
use App\Models\Department;
use App\Models\Membership;
use App\Models\Organization;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Services\TaskAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;
    //php artisan test --filter=
    public function test_service_allows_update_with_permission(){
        $membership = Mockery::mock(Membership::class);
        $account = Mockery::mock(Account::class);
        $task = Mockery::mock(Task::class);
        $department = Mockery::mock(Department::class);

        $account->shouldReceive('membershipForTask')
            ->once()
            ->with($task)
            ->andReturn($membership);

        $membership->shouldReceive('canAccessDepartment')
            ->once()
            ->with($department)
            ->andReturn(true);

        $membership->shouldReceive('getCachedPermissions')
            ->once()
            ->andReturn(['tasks.update' => true]);

        // 👇 این خط خیلی مهم است (اصلاح اصلی)
        $task->shouldReceive('getAttribute')
            ->once()
            ->with('department')
            ->andReturn($department);

        $service = new TaskAuthorizationService;

        $this->assertTrue(
            $service->canUpdate($account, $task)
        );
    }
    //---------------
    public function test_manager_can_create_task_in_own_department(){
        Cache::flush();
        
        // Setup
        $org = Organization::factory()->create();
        $department = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);
        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);
        $role = Role::factory()->create([
            'title' => 'Manager',
        ]);
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $department->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        
        // Debug: Check the basics
        $this->assertEquals('Manager', $account->roleTitleFor($department));
        
        // Test the Gate - IMPORTANT: Check the parameter order
        // Based on your policy method signature: create(Account $user, Department $department)
        // The Gate should pass Task::class and $department
        $result = Gate::forUser($account)->allows('create', [Task::class, $department]);
        
        $this->assertTrue($result, 'Manager should be able to create tasks in their department');
    }
    //---------------
    public function test_none_manager_can_not_create_task_in_own_department(){
        Cache::flush();
        
        // Setup
        $org = Organization::factory()->create();
        $department = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);
        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);
        $role = Role::factory()->create([
            'title' => 'operator',
        ]);
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $department->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        
        $this->assertEquals('operator', $account->roleTitleFor($department));
        $result = Gate::forUser($account)->allows('create', [Task::class, $department]);
        
        $this->assertFalse($result, 'None manager should not be able to create tasks in their department');
    }
    //---------------
    public function test_user_without_membership_cannot_create_task(){
        Cache::flush();
        
        // Setup
        $org = Organization::factory()->create();
        $department = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);
        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);
        
        $result = Gate::forUser($account)->allows('create', [Task::class, $department]);
        
        $this->assertFalse($result, 'None member should not be able to create tasks in departments');
    }
    //---------------
    public function test_suspended_membership_cannot_create_task(){
        Cache::flush();
        
        // Setup
        $org = Organization::factory()->create();
        $department = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);
        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);
        $role = Role::factory()->create([
            'title' => 'manager',
        ]);
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $department->id,
            'role_id' => $role->id,
            'status' => 'suspended',
        ]);
        
        //$this->assertEquals('manager', $account->roleTitleFor($department));
        //dump("result :",$this->assertEquals('manager', $account->roleTitleFor($department)));
        $result = Gate::forUser($account)->allows('create', [Task::class, $department]);
        
        $this->assertFalse($result, ' ');
    }
    //---------------
    public function test_user_with_multiple_memberships_uses_correct_department_for_create(){
        Cache::flush();

        $org = Organization::factory()->create();

        $deptA = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);

        $deptB = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);

        $roleManager = Role::factory()->create([
            'title' => 'manager',
        ]);

        $roleNoManager = Role::factory()->create([
            'title' => 'assistant',
        ]);

        // عضویت اول (بدون دسترسی)
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $deptA->id,
            'role_id' => $roleNoManager->id,
            'status' => 'active',
        ]);

        // عضویت دوم (با دسترسی)
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $deptB->id,
            'role_id' => $roleManager->id,
            'status' => 'active',
        ]);
        $this->assertEquals('manager', $account->roleTitleFor($deptB));
        $this->assertEquals('assistant', $account->roleTitleFor($deptA));

        $this->assertFalse(
            Gate::forUser($account)->allows('create', [Task::class, $deptA])
        );
        $this->assertTrue(
            Gate::forUser($account)->allows('create', [Task::class, $deptB])
        );
    }
    //---------------
    public function test_policy_allows_create_for_manager(){
        $org = Organization::factory()->create();
        $department = Department::factory()->create(['organization_id' => $org->id]);
        $manager = Account::factory()->create(['organization_id' => $org->id]);

        $role = Role::factory()->create(['title' => 'manager']);

        Membership::factory()->create([
            'account_id' => $manager->id,
            'department_id' => $department->id,
            'organization_id' => $org->id,
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $policy = new TaskPolicy(new TaskAuthorizationService());

        $this->assertTrue(
            $policy->create($manager, $department)
        );
    }
}