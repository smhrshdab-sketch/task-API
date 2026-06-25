<?php
use App\Models\Membership;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Tests\TestCase;

class PermissionInheritanceTest extends TestCase{
    public function test_permission_inheritance_works(){
        $operator = Role::factory()->create(['name' => 'Operator']);
        $supervisor = Role::factory()->create([
            'title' => 'Supervisor',
            'slug' => 'supervisor',
            'parent_id' => $operator->id
        ]);
        $manager = Role::factory()->create([
            'title' => 'Manager',
            'slug' => 'manager',
            'parent_id' => $supervisor->id
        ]);

        $p1 = Permission::factory()->create(['name' => 'p1']);
        $p2 = Permission::factory()->create(['name' => 'p2']);
        $p3 = Permission::factory()->create(['name' => 'p3']);

        $operator->permissions()->attach([$p1->id, $p2->id]);
        $supervisor->permissions()->attach([$p3->id]);

        $membership = Membership::factory()->create([
            'role_id' => $manager->id
        ]);

        $service = new PermissionService();

        $permissions = $service->resolvePermissions($membership);

        $this->assertEqualsCanonicalizing(
            ['p1', 'p2', 'p3'],
            $permissions
        );
    }
}