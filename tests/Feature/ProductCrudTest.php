<?php

namespace Tests\Feature;

use App\DTO\ProductDTO;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    private User $admin;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->service = new ProductService(new ProductRepository);

        $this->admin = User::factory()->create();
        $this->manager = User::factory()->create();

        $this->admin->syncRoles(Role::findOrCreate('admin'));
        $this->manager->syncRoles(Role::findOrCreate('manager'));
    }

    /**
     * @throws ValidationException
     */
    public function test_create_product_with_attributes(): void
    {
        $product = $this->service->create(new ProductDTO(
            name: 'Тестовый товар',
            categoryId: 2,
            price: '100.50',
            attributes: [['attribute_id' => 1, 'value' => 'Xiaomi']],
        ));

        $this->assertSame('Тестовый товар', $product->fresh()->name);
        $this->assertSame('100.50', $product->fresh()->price);
        $this->assertCount(1, $product->attributes()->get());
    }

    public function test_create_product_with_unknown_category_fails(): void
    {
        try {
            $this->service->create(new ProductDTO(name: 'Товар', categoryId: 999));
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('category_id', $e->errors());
        }
    }

    /**
     * @throws ValidationException
     */
    public function test_update_product(): void
    {
        $product = Product::query()->firstOrFail();

        $updated = $this->service->update($product, new ProductDTO(name: 'Новое название', price: '999.00'));

        $this->assertSame('Новое название', $updated->fresh()->name);
        $this->assertSame('999.00', $updated->fresh()->price);
    }

    public function test_delete_product_removes_attributes(): void
    {
        $product = Product::query()->whereHas('attributes')->firstOrFail();
        $this->assertNotCount(0, $product->attributes()->get());

        $this->service->delete($product);

        $this->assertNull(Product::query()->find($product->id));
        $this->assertCount(0, ProductAttribute::query()->where('product_id', $product->id)->get());
    }

    public function test_admin_sees_delete_action(): void
    {
        $product = Product::query()->firstOrFail();

        $this->actingAs($this->admin);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->assertActionExists([
                'name' => 'delete',
                'context' => ['table' => true, 'recordKey' => $product],
            ]);
    }

    public function test_admin_can_delete_product_manager_cannot(): void
    {
        $product = Product::query()->firstOrFail();

        $this->actingAs($this->admin);
        $this->assertTrue(ProductResource::getDeleteAuthorizationResponse($product)->allowed());

        $this->actingAs($this->manager);
        $this->assertFalse(ProductResource::getDeleteAuthorizationResponse($product)->allowed());
    }

    public function test_manager_sees_edit_action(): void
    {
        $product = Product::query()->firstOrFail();

        $this->actingAs($this->manager);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->assertActionExists([
                'name' => 'edit',
                'context' => ['table' => true, 'recordKey' => $product],
            ]);
    }
}
