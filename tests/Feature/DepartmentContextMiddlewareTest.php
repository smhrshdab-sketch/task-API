<?php

use App\Models\Account;
use App\Models\Department;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentContextMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    //php artisan test --filter=
    public function test_request_fails_without_department_header()
    {
        $user = Account::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/dashboard')
            ->assertStatus(400);
    }

    public function test_request_passes_with_valid_membership()
    {
        $user = Account::factory()->create();
        $department = Department::factory()->create();

        Membership::factory()->create([
            'account_id' => $user->id,
            'department_id' => $department->id,
        ]);

        $this->actingAs($user, 'api')
            ->withHeader('X-Department-Id', $department->id)
            ->getJson('/api/dashboard')
            ->assertStatus(200);
    }
}
