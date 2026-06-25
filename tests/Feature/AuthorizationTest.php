<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\Role;
use App\Models\Task;
use App\Models\Account;
use App\Models\Department;
use App\Models\Membership;
use App\Models\Organization;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Services\TaskAuthorizationService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Auth\Autenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthorizationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
     //php artisan test --filter=test_user_cannot_update_task_without_permission

   public function test_user_can_update_task_with_valid_permission(){
        // Arrange
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create();

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        // Act
        $response = $this
        ->actingAs($account, 'api')
        ->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated title',
            'status' => 'open',
            'priority' => 'high',
        ]);

        // Assert
        $response->assertStatus(200);
    }
    //--------- second test -----------   
    public function test_user_cannot_update_task_without_permission(){
        // Arrange
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        // ❌ نقش بدون permission update
        $role = Role::factory()->create([
            'permissions' => json_encode(['tasks.view']),
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        // Act
        $token = auth('api')->login($account);
        // dump("Token generated: " . ($token ? 'Yes' : 'No'));
        // dump("Auth check: " . (auth('api')->check() ? 'Yes' : 'No'));
        // dump("Authenticated user ID: " . (auth('api')->id() ?? 'None'));

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Hacked title 😈',
                'status' => 'open',
                'priority' => 'low',
            ]);
        // dump("Status: " . $response->status());
        // dump("Content: " . $response->content());       

        // Assert
        $response->assertStatus(403);
        //$response->dump();

    }
    //===================
    public function test_permission_cache_is_invalidated_when_role_changes(){
        Cache::flush();

        $organization = Organization::factory()->create();
        $department   = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.view'], // هنوز update نداره
        ]);

        $membership = Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAs($account, 'api');
        //Act – بار اول (cache ساخته می‌شود)
        $assert = $this->assertFalse(
            Gate::forUser($account)->allows('update', $task)
        );
        dump("assert1  : ",$assert);
        /*اینجا:
            permissionها cache شدند
            نتیجه: ❌
        */
        //تغییر نقش + invalidate cache
        $role->update([
            'permissions' => ['tasks.view', 'tasks.update'],
        ]);
        //dump("permissions check array : " . (is_array($role->permissions) ? 'Yes' : 'No'));
        dump("role->permissions :",$role->permissions);
        Cache::forget("permissions:membership:{$membership->id}");
        //(یا اگه event داری، اون باید cache رو پاک کنه)
        //Act – بار دوم (بعد از invalidate)
        //$membership->refresh();
        //$membership->load('role');
        //$membership->unsetRelation('role');
        //Gate::flush();
        //Gate::forgetCachedPermissions(); //
        $this->refreshApplication();

        $assert = $this->assertTrue(
                Gate::forUser($account)->allows('update', $task)
            );
            dump("assert2  : ",$assert);
    }
    //--------------
    public function test_user_can_update_task_with_permission_override(){
        Cache::flush();
        $organization = Organization::factory()->create();
        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);
        $account = Account::factory()->create();
        $role = Role::factory()->create([
            'permissions' => ['tasks.view'], // update
        ]);
        $membership = Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $organization->id,
            'department_id' => $department->id,
            'role_id' => $role->id,
                'permissions_override' => [
                'tasks.update' => true,
                ],
            ]);
        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);
        $this->actingAs($account, 'api');
        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
    }
//-------------------پ
    public function test_user_cannot_update_task_in_parent_department(){
        $org = Organization::factory()->create();

        $parent = Department::factory()->create([
            'organization_id' => $org->id,
        ]);

        $child = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $parent->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);

        // کاربر فقط عضو واحد فرزند است
        Membership::factory()->create([
            'account_id' => $account->id,
            'department_id' => $child->id,
            'role_id' => Role::factory()->create([
                'permissions' => ['tasks.update']
            ])->id,
        ]);

        $taskInParent = Task::factory()->create([
            'department_id' => $parent->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $taskInParent)
        );
    }
    //-----------
    public function test_user_can_update_task_in_child_department(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $parent = Department::factory()->root()->create([
            'organization_id' => $organization->id,
            'path' => '1',
        ]);

        $child = Department::factory()->create([
            'organization_id' => $organization->id,
            'parent_id' => $parent->id,
            'path' => '1.2',
        ]);

        $account = Account::factory()->create();

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $organization->id,
            'department_id' => $parent->id, // اینجا parent
            'role_id' => $role->id,
        ]);

        $task = Task::factory()->create([
            'department_id' => $child->id, // تسک تو child
        ]);

       // dump("Test: is_membership_done : ".$is_membership_done,$this->actingAs($account, 'api'));

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
    }
    //------------
    public function test_user_cannot_update_task_if_membership_is_suspended(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'suspended'
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task)
        );
    }
     //---------
    public function test_user_cannot_update_task_if_membership_is_active(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
    }
    //---------
    public function test_suspended_membership_invalidates_cached_permission(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

       $membership = Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);        
        $membership->getCachedPermissions();

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $membership->update(['status' => 'suspended']);

        $this->actingAs($account, 'api');

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task)
        );
    }
    //----------
    public function test_reactiving_membership_restores_access(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

       $membership = Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);        
        $membership->getCachedPermissions();

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);
        $this->actingAs($account, 'api');
        $this->assertTrue(Gate::forUser($account)->allows('update', $task));
        $membership->update(['status' => 'suspended']);
        $this->assertFalse(Gate::forUser($account)->allows('update', $task));
        $membership->update(['status' => 'active']);
        $this->assertTrue(Gate::forUser($account)->allows('update', $task));
    }
    //----------
    public function test_changing_role_permissions_affects_task_update_access(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
        //..........
        $role->update(['permissions' => ['tasks.view']]);

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task)
        );
    }
    //--------------
    public function test_changing_role_permissions_restores_access_when_permission_added_back(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);

        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
        //..........
        $role->update(['permissions' => ['tasks.view']]);

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task)
        );
     //..........
        $role->update(['permissions' => ['tasks.view','tasks.update']]);

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task)
        );
    }
    //--------------
    public function test_user_cannot_update_task_in_other_department(){
        // Arrange
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
            'path' => '3.1'
        ]);
        $dad = Department::factory()->root()->create([
            'organization_id' => $organization->id,
            'path' => '3'
        ]);
        $child = Department::factory()->root()->create([
            'organization_id' => $organization->id,
            'path' => '3.1.2'
        ]);
        $cosin = Department::factory()->root()->create([
            'organization_id' => $organization->id,
            'path' => '2.2'
        ]);

        $account = Account::factory()->create();

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
        ]);

        $task_me = Task::factory()->create([
            'department_id' => $department->id,
        ]);
        $task_dad = Task::factory()->create([
            'department_id' => $dad->id,
        ]);
         $task_son = Task::factory()->create([
            'department_id' => $child->id,
        ]);
        $task_cos = Task::factory()->create([
            'department_id' => $cosin->id,
        ]);

        $this->actingAs($account, 'api');

        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task_me)
        );
        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task_dad)
        );
        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task_son)
        );
        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task_cos)
        );
    }
    //-------------
    public function test_user_with_multiple_memberships_uses_correct_one(){
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

        $roleNoUpdate = Role::factory()->create([
            'permissions' => ['tasks.view'],
        ]);

        $roleWithUpdate = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        // عضویت اول (بدون دسترسی)
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $deptA->id,
            'role_id' => $roleNoUpdate->id,
            'status' => 'active',
        ]);

        // عضویت دوم (با دسترسی)
        Membership::factory()->create([
            'account_id' => $account->id,
            'organization_id' => $org->id,
            'department_id' => $deptB->id,
            'role_id' => $roleWithUpdate->id,
            'status' => 'active',
        ]);

        $task_a = Task::factory()->create([
            'department_id' => $deptA->id,
        ]);
        $task_b = Task::factory()->create([
            'department_id' => $deptB->id,
        ]);
        //dump("Account :",$account->memberships," Task A: ",$task_a," Task B: ",$task_b);
        $this->actingAs($account, 'api');

        $this->assertFalse(
            Gate::forUser($account)->allows('update', $task_a)
        );
        $this->assertTrue(
            Gate::forUser($account)->allows('update', $task_b)
        );
        // $this->assertTrue(
        //     Gate::forUser($account)->allows('view', $task_a)
        // );
        // $this->assertFalse(
        //     Gate::forUser($account)->allows('view', $task_b)
        // );
    }
    //=============
    public function test_user_can_delete_task_with_valid_permission(){
        Cache::flush();

        $organization = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);
        $account = Account::factory()->create();
        $role = Role::factory()->create([
            'permissions' => ['tasks.delete'],
        ]);
        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
        ]);
        $task = Task::factory()->create([
            'department_id' => $department->id,
        ]);

        $response = $this
            ->actingAs($account, 'api')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertSoftDeleted();
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
    public function test_not_admin_can_not_create_department(){
        Cache::flush();

        $organization = Organization::factory()->create();
        
        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['tasks.update'],
        ]);

        $membership = Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);
        //dump($organization,$department,$role,$membership);departments.create
        $result = Gate::forUser($account)->allows('create', [Department::class, $department]);
        
        $this->assertFalse($result, 'normal user should not be able to create new department');
    }
    public function test_admin_can_create_department(){
        Cache::flush();

        $organization = Organization::factory()->create();
        
        $department = Department::factory()->root()->create([
            'organization_id' => $organization->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $role = Role::factory()->create([
            'permissions' => ['departments.create'],
        ]);

        $membership = Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $organization->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active'
        ]);
        //dump($organization,$department,$role,$membership);departments.create
        $result = Gate::forUser($account)->allows('create', [Department::class, $department]);
        
        $this->assertTrue($result, 'Admin user should be able to create new department');
    }
}