<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase{
    use RefreshDatabase;
    //============ php artisan test --filter=test_creating_account_does_not_create_membership =========
    public function test_creating_account_does_not_create_membership(){
        $account = Account::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('accounts', [
            'email' => 'test@example.com',
        ]);
        
        $this->assertDatabaseCount('memberships', 0);
    }
    //=================
    public function test_registering_account_does_not_create_membership(){
        $response = $this->postJson('/api/register', [
            'name' => 'Sara Kenny',
            'email' => 's.kenny@gmail.com',
            'password' => '12345678',
            'bio' => 'some stuff!!',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('accounts', [
            'email' => 's.kenny@gmail.com',
        ]);

        $this->assertDatabaseCount('memberships', 0);
    }
    //=================
    public function test_login_returns_active_memberships_for_account(){
        $targetAccount = Account::factory()->create([
            'name' => 'Sara Kenny',
            'email' => 's.kenny@gmail.com',
            'bio' => 'some stuff!!',
        ]);

        $strangerAccount = Account::factory()->create();

        $membership1 = Membership::factory()->create([
            'account_id' => $targetAccount->id,
            'status' => 'active',
        ]);

        $membership2 = Membership::factory()->create([
            'account_id' => $targetAccount->id,
            'status' => 'suspended',
        ]);

        $membership3 = Membership::factory()->create([
            'account_id' => $strangerAccount->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 's.kenny@gmail.com',
            'password' => 'password',
        ]);

        $response->assertSuccessful();

        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $targetAccount->id,
                'email' => 's.kenny@gmail.com',
            ],
        ]);

        $response->assertJsonFragment([
            'id' => $membership1->id,
        ]);

        $response->assertJsonMissing([
            'id' => $membership2->id,
        ]);

        $response->assertJsonMissing([
            'id' => $membership3->id,
        ]);
    }
}