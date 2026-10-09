<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $userWithoutRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();
        $this->userWithoutRole = User::factory()->create();

        $this->admin->syncRoles(Role::findOrCreate('admin'));
        $this->manager->syncRoles(Role::findOrCreate('manager'));
    }

    public function test_admin_can_do_everything(): void
    {
        $product = Product::query()->first();
        $category = Category::query()->first();
        $attribute = Attribute::query()->first();

        $this->assertTrue($this->admin->can('viewAny', Product::class));
        $this->assertTrue($this->admin->can('create', Product::class));
        $this->assertTrue($this->admin->can('update', $product));
        $this->assertTrue($this->admin->can('delete', $product));
        $this->assertTrue($this->admin->can('deleteAny', Product::class));

        $this->assertTrue($this->admin->can('viewAny', Category::class));
        $this->assertTrue($this->admin->can('create', Category::class));
        $this->assertTrue($this->admin->can('update', $category));
        $this->assertTrue($this->admin->can('delete', $category));
        $this->assertTrue($this->admin->can('deleteAny', Category::class));

        $this->assertTrue($this->admin->can('viewAny', Attribute::class));
        $this->assertTrue($this->admin->can('create', Attribute::class));
        $this->assertTrue($this->admin->can('update', $attribute));
        $this->assertTrue($this->admin->can('delete', $attribute));
        $this->assertTrue($this->admin->can('deleteAny', Attribute::class));
    }

    public function test_manager_can_manage_products_but_not_delete_them(): void
    {
        $product = Product::query()->first();

        $this->assertTrue($this->manager->can('viewAny', Product::class));
        $this->assertTrue($this->manager->can('create', Product::class));
        $this->assertTrue($this->manager->can('update', $product));
        $this->assertFalse($this->manager->can('delete', $product));
        $this->assertFalse($this->manager->can('deleteAny', Product::class));
    }

    public function test_manager_can_manage_categories_but_not_delete_them(): void
    {
        $category = Category::query()->first();

        $this->assertTrue($this->manager->can('viewAny', Category::class));
        $this->assertTrue($this->manager->can('create', Category::class));
        $this->assertTrue($this->manager->can('update', $category));
        $this->assertFalse($this->manager->can('delete', $category));
        $this->assertFalse($this->manager->can('deleteAny', Category::class));
    }

    public function test_manager_cannot_touch_attributes(): void
    {
        $attribute = Attribute::query()->first();

        $this->assertFalse($this->manager->can('viewAny', Attribute::class));
        $this->assertFalse($this->manager->can('create', Attribute::class));
        $this->assertFalse($this->manager->can('update', $attribute));
        $this->assertFalse($this->manager->can('delete', $attribute));
        $this->assertFalse($this->manager->can('deleteAny', Attribute::class));
    }

    public function test_user_without_role_has_no_access(): void
    {
        $this->assertFalse($this->userWithoutRole->can('viewAny', Product::class));
        $this->assertFalse($this->userWithoutRole->can('create', Product::class));
        $this->assertFalse($this->userWithoutRole->can('viewAny', Category::class));
        $this->assertFalse($this->userWithoutRole->can('create', Category::class));
        $this->assertFalse($this->userWithoutRole->can('viewAny', Attribute::class));
        $this->assertFalse($this->userWithoutRole->can('create', Attribute::class));
    }
}
