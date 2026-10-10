<?php

namespace Tests\Feature;

use App\DTO\CategoryDTO;
use App\Models\Category;
use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    private User $admin;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->service = new CategoryService(new CategoryRepository);

        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();

        $this->admin->syncRoles(Role::findOrCreate('admin'));
        $this->manager->syncRoles(Role::findOrCreate('manager'));
    }

    public function test_create_category(): void
    {
        $category = $this->service->create(new CategoryDTO(name: 'Новая категория', sortOrder: 5));

        $this->assertNotNull($category->fresh());
        $this->assertSame('Новая категория', $category->fresh()->name);
        $this->assertSame(5, $category->fresh()->sort_order);
    }

    public function test_create_category_with_parent(): void
    {
        $parent = Category::query()->where('name', 'Электроника')->firstOrFail();

        $category = $this->service->create(new CategoryDTO(name: 'Планшеты', parentId: $parent->id));

        $this->assertSame($parent->id, $category->fresh()->parent_id);
    }

    /**
     * @throws ValidationException
     */
    public function test_update_category(): void
    {
        $category = Category::query()->where('name', 'Ноутбуки')->firstOrFail();

        $updated = $this->service->update($category, new CategoryDTO(name: 'Ноутбуки и ультрабуки', sortOrder: 7));

        $this->assertSame('Ноутбуки и ультрабуки', $updated->fresh()->name);
        $this->assertSame(7, $updated->fresh()->sort_order);
    }

    public function test_cannot_move_category_into_itself(): void
    {
        $category = Category::query()->where('name', 'Электроника')->firstOrFail();

        try {
            $this->service->update($category, new CategoryDTO(name: 'Электроника', parentId: $category->id));
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }
    }

    public function test_cannot_move_category_into_its_descendant(): void
    {
        $root = Category::query()->where('name', 'Электроника')->firstOrFail();
        $child = Category::query()->where('name', 'Смартфоны')->firstOrFail();

        try {
            $this->service->update($root, new CategoryDTO(name: 'Электроника', parentId: $child->id));
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('parent_id', $e->errors());
        }
    }

    public function test_cannot_delete_category_with_children(): void
    {
        $root = Category::query()->where('name', 'Электроника')->firstOrFail();

        try {
            $this->service->delete($root);
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('category', $e->errors());
        }

        $this->assertNotNull($root->fresh());
    }

    public function test_cannot_delete_category_with_products(): void
    {
        $leaf = Category::query()->where('name', 'Смартфоны')->firstOrFail();
        $this->assertNotCount(0, $leaf->products()->get());

        try {
            $this->service->delete($leaf);
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('category', $e->errors());
        }

        $this->assertNotNull($leaf->fresh());
    }

    /**
     * @throws ValidationException
     */
    public function test_delete_empty_category(): void
    {
        $category = $this->service->create(new CategoryDTO(name: 'Пустая категория'));

        $this->service->delete($category);

        $this->assertNull(Category::query()->find($category->id));
    }

    public function test_admin_can_delete_category_manager_cannot(): void
    {
        $category = Category::query()->where('name', 'Смартфоны')->firstOrFail();

        $this->assertTrue($this->admin->can('delete', $category));
        $this->assertFalse($this->manager->can('delete', $category));
    }
}
