<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Permissions\Permission as MyPermissions;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $role;
    

    public function setUp(): void
    {
        parent::setUp();
        //    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
        $this->user = User::factory()->create();
        $testPermission = 'create-payment-method';
        Permission::create(['guard_name' => 'api', 'name' => $testPermission]);
        $this->role = Role::create(['name' => 'user']);
        $this->user->assignRole($this->role);
        $this->role->givePermissionTo('create-payment-method');
    }

    /** @test */
    public function ok_is_returned_if_the_user_has_permission()
    {
        // dd($this->user);
        $this->actingAs($this->user)
            ->post('/api/paymentmethod/create', [
                'user_id' => $this->user->id,
                'card_name' => 'userhp',
                'card_number' =>'123456789101121',
                'expire_date' => '12/12/2025',
                'type' => 'masterCard',
            ])
            ->assertStatus(200);
    }
    /** @test */
    public function acces_is_denied_if_the_user_does_not_have_permission()
    {
        $this->role->revokePermissionTo('create-payment-method');

        $this->actingAs($this->user)
            ->post('/api/paymentmethod/create')
            ->assertStatus(403);
    }

    public function test_new_users_can_register()
    {   
        // $userRole = Role::create(['name' => 'user']);
        $response = $this->post('/api/register', [
            'username' => 'Test User',
            'email' => 'buri@bestcolombian.com',
            'password' => '12345678',
            'phone' => '123456789'
        ]);
        
        $response->assertStatus(200);
    }

}
