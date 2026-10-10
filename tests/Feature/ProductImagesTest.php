<?php

namespace Tests\Feature;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->service = new ProductService(new ProductRepository);

        Storage::fake('public');
    }

    /**
     * @throws ValidationException
     */
    public function test_create_product_persists_images(): void
    {
        Storage::disk('public')->put('products/first.jpg', 'img-1');
        Storage::disk('public')->put('products/second.jpg', 'img-2');

        $product = $this->service->create(new ProductDTO(
            name: 'Товар с фото',
            price: '100.00',
            images: ['products/first.jpg', 'products/second.jpg'],
        ));

        $this->assertSame(['products/first.jpg', 'products/second.jpg'], $product->fresh()->images);
        $this->assertTrue(Storage::disk('public')->exists('products/first.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('products/second.jpg'));
    }

    /**
     * @throws ValidationException
     */
    public function test_update_product_replaces_images(): void
    {
        Storage::disk('public')->put('products/old.jpg', 'img-old');
        Storage::disk('public')->put('products/new.jpg', 'img-new');

        $product = $this->service->create(new ProductDTO(
            name: 'Товар',
            images: ['products/old.jpg'],
        ));

        $this->service->update($product, new ProductDTO(name: 'Товар', images: ['products/new.jpg']));

        $this->assertSame(['products/new.jpg'], $product->fresh()->images);
        $this->assertFalse(Storage::disk('public')->exists('products/old.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('products/new.jpg'));
    }

    /**
     * @throws ValidationException
     */
    public function test_update_product_removes_all_images(): void
    {
        Storage::disk('public')->put('products/one.jpg', 'img');
        Storage::disk('public')->put('products/two.jpg', 'img');

        $product = $this->service->create(new ProductDTO(
            name: 'Товар',
            images: ['products/one.jpg', 'products/two.jpg'],
        ));

        $this->service->update($product, new ProductDTO(name: 'Товар', images: []));

        $this->assertSame([], $product->fresh()->images);
        $this->assertFalse(Storage::disk('public')->exists('products/one.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('products/two.jpg'));
    }

    /**
     * @throws ValidationException
     */
    public function test_delete_product_removes_images(): void
    {
        Storage::disk('public')->put('products/a.jpg', 'img');
        Storage::disk('public')->put('products/b.jpg', 'img');

        $product = $this->service->create(new ProductDTO(
            name: 'Товар',
            images: ['products/a.jpg', 'products/b.jpg'],
        ));

        $this->service->delete($product);

        $this->assertNull(Product::query()->find($product->id));
        $this->assertFalse(Storage::disk('public')->exists('products/a.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('products/b.jpg'));
    }
}
