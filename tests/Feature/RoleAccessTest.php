<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();

        $this->admin->syncRoles(Role::findOrCreate('admin'));
        $this->manager->syncRoles(Role::findOrCreate('manager'));
    }

    public function test_admin_can_open_attributes_page(): void
    {
        $this->actingAs($this->admin)->get('/admin/attributes')->assertStatus(200);
    }

    public function test_manager_cannot_open_attributes_page(): void
    {
        $this->actingAs($this->manager)->get('/admin/attributes')->assertStatus(403);
    }

    public function test_manager_can_open_products_page(): void
    {
        $this->actingAs($this->manager)->get('/admin/products')->assertStatus(200);
    }

    public function test_manager_can_open_categories_page(): void
    {
        $this->actingAs($this->manager)->get('/admin/categories')->assertStatus(200);
    }

    public function test_user_without_role_cannot_open_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/products')->assertStatus(403);
    }
}
