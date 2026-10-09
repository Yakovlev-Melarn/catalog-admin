<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductTableFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $admin = User::factory()->create();
        $admin->syncRoles(Role::findOrCreate('admin'));

        $this->actingAs($admin);
    }

    public function test_search_by_name(): void
    {
        $products = Product::query()->where('name', 'like', '%iPhone%')->get();

        $this->assertNotCount(0, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->set('tableSearch', 'iPhone')
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords($products->count());
    }

    public function test_search_by_sku(): void
    {
        $products = Product::query()->where('sku', 'like', '%SKU-0003%')->get();

        $this->assertCount(1, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->set('tableSearch', 'SKU-0003')
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords(1);
    }

    public function test_category_filter_includes_descendants(): void
    {
        $root = Category::query()->where('name', 'Электроника')->firstOrFail();

        $ids = [(int) $root->id];
        $parents = [(int) $root->id];

        while ($parents !== []) {
            $childIds = Category::query()->where('parent_id', $parents)->pluck('id')->map(static fn ($id): int => (int) $id)->all();
            $ids = array_merge($ids, $childIds);
            $parents = $childIds;
        }

        $products = Product::query()->whereIn('category_id', $ids)->get();

        $this->assertNotCount(0, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->filterTable('category', $root->id)
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords($products->count());
    }

    public function test_category_filter_leaf_category(): void
    {
        $leaf = Category::query()->where('name', 'Смартфоны')->firstOrFail();

        $products = Product::query()->where('category_id', $leaf->id)->get();

        $this->assertNotCount(0, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->filterTable('category', $leaf->id)
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords($products->count());
    }

    public function test_attribute_filter(): void
    {
        $brand = Attribute::query()->where('name', 'Бренд')->firstOrFail();

        $products = Product::query()
            ->whereHas('attributes', static fn ($query) => $query->where('attribute_id', $brand->id))
            ->get();

        $this->assertNotCount(0, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->filterTable('attribute', $brand->id)
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords($products->count());
    }

    public function test_attribute_value_filter(): void
    {
        $brand = Attribute::query()->where('name', 'Бренд')->firstOrFail();

        $products = Product::query()
            ->whereHas('attributes', static fn ($query) => $query->where('attribute_id', $brand->id)->where('value', 'Apple'))
            ->get();

        $this->assertNotCount(0, $products);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->filterTable('attribute', $brand->id)
            ->filterTable('attribute_value', 'Apple')
            ->assertCanSeeTableRecords($products)
            ->assertCountTableRecords($products->count());
    }

    public function test_attribute_value_filter_is_hidden_without_attribute(): void
    {
        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->assertTableFilterHidden('attribute_value');

        $brand = Attribute::query()->where('name', 'Бренд')->firstOrFail();

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->filterTable('attribute', $brand->id)
            ->assertTableFilterVisible('attribute_value');
    }

    public function test_sort_by_price(): void
    {
        $expected = Product::query()->orderBy('price', 'asc')->get()->take(10);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->call('sortTable', 'price', 'asc')
            ->assertCanSeeTableRecords($expected, inOrder: true);
    }

    public function test_sort_by_stock(): void
    {
        $expected = Product::query()->orderBy('stock', 'desc')->get()->take(10);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->call('sortTable', 'stock', 'desc')
            ->assertCanSeeTableRecords($expected, inOrder: true);
    }

    public function test_sort_by_created_at(): void
    {
        $index = 0;

        foreach (Product::query()->orderBy('id')->get() as $product) {
            $product->forceFill(['created_at' => now()->subDays(21 - $index)])->save();
            $index++;
        }

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->call('sortTable', 'created_at', 'asc')
            ->assertCanSeeTableRecords(Product::query()->orderBy('id')->get()->take(10), inOrder: true);

        Livewire::test(ListProducts::class)
            ->set('tableRecordsPerPage', 'all')
            ->call('sortTable', 'created_at', 'desc')
            ->assertCanSeeTableRecords(Product::query()->orderByDesc('id')->get()->take(10), inOrder: true);
    }
}
