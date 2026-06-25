<?php

namespace Tests\Feature\Task;

use App\Models\Account;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Membership;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;
    //php artisan test --filter=

    public function test_manager_can_create_task_via_api(){
        // ---------------- Arrange ----------------
        $org = Organization::factory()->create();

        $department = Department::factory()->root()->create([
            'organization_id' => $org->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $org->id,
        ]);

        $account = Account::factory()->create([
            'organization_id' => $org->id,
        ]);

        $role = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
        ]);

        Membership::factory()->create([
            'account_id'      => $account->id,
            'organization_id' => $org->id,
            'department_id'   => $department->id,
            'role_id'         => $role->id,
            'status'          => 'active',
        ]);

        // داده‌ای که کاربر می‌فرستد
        $payload = [
            'project_id'  => $project->id,
            'assignee_id' => $account->id,
            'title'       => 'Fix authentication bug',
            'status'      => 'open',
            'description' => 'Something is broken',
            'priority'    => 'high',
            'deadline'    => '2026-02-20',
        ];

        // ---------------- Act ----------------
        $response = $this->actingAs($account, 'api')
            ->postJson("/api/departments/{$department->id}/tasks", $payload);

        // ---------------- Assert ----------------
        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'project_id'    => $project->id,
            'department_id' => $department->id,
            'assignee_id'   => $account->id,
            'title'         => 'Fix authentication bug',
            'status'        => 'open',
            'priority'      => 'high',
        ]);
    }
}