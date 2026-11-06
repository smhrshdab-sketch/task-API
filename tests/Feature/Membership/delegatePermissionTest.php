<?php

namespace Tests\Feature\Membership;

use App\Models\Account;
use App\Models\Department;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegatePermissionTest extends TestCase{
    use RefreshDatabase;
    public function test_manager_membership_can_delegate_permission_to_membership_in_scope(){
        $org = Organization::withoutEvents(function () {    //هنگام ساخت Organization رویدادهای Eloquent را غیرفعال
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,      // اگر که اینو نزاری خودش بصورت شانسی مقدار میده
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true,
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([       //برای پرکردن جدول  ارتباطی 
            $viewPermission->id,
            $updatePermission->id,
            $managePermissions->id,
        ]);
        
        $operatorRole->permissions()->attach([      //برای پرکردن جدول  ارتباطی 
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $secondLevelDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [   //یک ارزیابی / چک ساده و شاید غیر لازم
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')   //برای احراز هوییت وعبور از میدلویر
        ->withHeader(
            'X-Department-Id',      //  برای تعیین عضویت جاری لازم است
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertNoContent();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseHas('membership_permission', [ //برای چک کردن وجود این رکورد ها در پایگاه داده
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_cannot_delegate_non_delegable_permission(){
        $org = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,      
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => false,    //whole point
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([ 
            $viewPermission->id,
            $updatePermission->id,
            $managePermissions->id,
        ]);
        
        $operatorRole->permissions()->attach([      
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $secondLevelDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')  
        ->withHeader(
            'X-Department-Id',    
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertForbidden();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseMissing('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_cannot_delegate_non_manage_permissions_permission(){
        $org = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,      
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true,
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([ 
            $viewPermission->id,
            $updatePermission->id,
        ]);
        
        $operatorRole->permissions()->attach([      
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $secondLevelDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')  
        ->withHeader(
            'X-Department-Id',    
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertForbidden();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseMissing('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_cannot_delegate_permission_without_the_permission(){
        $org = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,      
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true,
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([ 
            $viewPermission->id,
            $managePermissions->id,
        ]);
        
        $operatorRole->permissions()->attach([      
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $secondLevelDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')  
        ->withHeader(
            'X-Department-Id',    
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertForbidden();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseMissing('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_cannot_delegates_permission_out_of_scope(){
        $org = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,      
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
            'path' => 1
        ]);
        $sibilingFirstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
            'path' => 2
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        $sibilingFirstLevelDepartmentChild = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $sibilingFirstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true,
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([ 
            $viewPermission->id,
            $updatePermission->id,
            $managePermissions->id,
        ]);
        
        $operatorRole->permissions()->attach([      
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $sibilingFirstLevelDepartmentChild->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')  
        ->withHeader(
            'X-Department-Id',    
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertForbidden();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseMissing('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_cannot_delegate_to_out_of_organization(){
        $sourceOrg = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        $destOrg = Organization::withoutEvents(function () {  
            return Organization::factory()->create();
        });
        $account1 = Account::factory()->create([
            'organization_id' => $sourceOrg->id,      
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $destOrg->id,
        ]);        

        $sourceRoot = Department::factory()->create([
            'organization_id' => $sourceOrg->id,
        ]);
        $sourceDepartment = Department::factory()->create([
            'organization_id' => $sourceOrg->id,
            'parent_id' => $sourceRoot->id,
        ]);
        $destRoot = Department::factory()->create([
            'organization_id' => $destOrg->id,
        ]);
        $destDepartment = Department::factory()->create([
            'organization_id' => $destOrg->id,
            'parent_id' => $destRoot->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([ 
            $viewPermission->id,
            $updatePermission->id,
            $managePermissions->id,
        ]);
        
        $operatorRole->permissions()->attach([      
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $sourceOrg->id,
            'department_id' => $sourceDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $destOrg->id,
            'department_id' => $destDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $sourceDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api')  
        ->withHeader(
            'X-Department-Id',    
            $sourceDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertForbidden();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseMissing('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
    //
    public function test_manager_membership_can_delegate_specific_membership_permission_to_membership(){
        $org = Organization::withoutEvents(function () { 
            return Organization::factory()->create();
        });
        
        $account1 = Account::factory()->create([
            'organization_id' => $org->id,   
        ]);
        
        $account2 = Account::factory()->create([
            'organization_id' => $org->id,
        ]);        

        $rootDep = Department::factory()->create([
            'organization_id' => $org->id,
        ]);
        $firstLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $rootDep->id,
        ]);
        $secondLevelDepartment = Department::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $firstLevelDepartment->id,
        ]);
        //===============
        $viewPermission = Permission::factory()->create([
            'slug' => 'department_view',
            'target' => 'department',
            'subject' => 'view',
            'is_delegable' => true,
        ]);
        
        $updatePermission = Permission::factory()->create([
            'slug' => 'department_update',
            'target' => 'department',
            'subject' => 'update',
            'is_delegable' => true,
        ]);
        
        $managePermissions = Permission::factory()->create([
            'slug' => 'membership_manage_permissions',
            'target' => 'membership',
            'subject' => 'manage_permissions',
            'is_delegable' => false,
        ]);
        //============================
        $managerRole = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);
        
        $operatorRole = Role::factory()->create([
            'title' => 'Operator',
            'slug' => 'operator',
        ]);
        //=============================
        $managerRole->permissions()->attach([      
            $viewPermission->id,
            $managePermissions->id,            
        ]);
        
        $operatorRole->permissions()->attach([   
            $viewPermission->id,
        ]);
        //----------------------------
        $managerMembership = Membership::factory()->create([
            'account_id' => $account1->id,
            'organization_id' => $org->id,
            'department_id' => $firstLevelDepartment->id,
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);
        $managerMembership->permissions()->attach([      
            $viewPermission->id,
            $managePermissions->id,
            $updatePermission->id
        ]);
        $operatorMembership = Membership::factory()->create([
            'account_id' => $account2->id,
            'organization_id' => $org->id,
            'department_id' => $secondLevelDepartment->id,
            'role_id' => $operatorRole->id,
            'status' => 'active',
        ]);        
        //=============================
        $this->assertDatabaseHas('memberships', [ 
            'id' => $managerMembership->id,
            'account_id' => $account1->id,
            'department_id' => $firstLevelDepartment->id,
        ]);
        //=============================
        $response = $this->actingAs($account1, 'api') 
        ->withHeader(
            'X-Department-Id',     
            $firstLevelDepartment->id
        )->postJson(
            "api/memberships/{$operatorMembership->id}/permissions/{$updatePermission->id}"     //ای پی آی برای به حرکت در آوردن کنترلر با همین ورودی ها
        );

        $response->assertNoContent();
        // dd([
        //     'status' => $response->status(),
        //     'body' => $response->json(),
        // ]);

        $this->assertDatabaseHas('membership_permission', [ 
            'membership_id' => $operatorMembership->id,
            'permission_id' => $updatePermission->id,
        ]);
    }
}