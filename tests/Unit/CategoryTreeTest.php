<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTreeTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->repository = new CategoryRepository;
    }

    public function test_tree_contains_only_roots(): void
    {
        $tree = $this->repository->getTree();

        $this->assertCount(2, $tree);
        $this->assertSame('Электроника', $tree->first()->name);
        $this->assertSame('Одежда', $tree->last()->name);
    }

    public function test_tree_nests_children(): void
    {
        $tree = $this->repository->getTree();
        $electronics = $tree->first(fn (Category $category): bool => $category->name === 'Электроника');

        $this->assertCount(2, $electronics->children);
        $this->assertSame('Смартфоны', $electronics->children->first()->name);
        $this->assertSame('Ноутбуки', $electronics->children->last()->name);
    }

    public function test_get_children(): void
    {
        $root = Category::query()->where('name', 'Одежда')->firstOrFail();

        $children = $this->repository->getChildren($root->id);

        $this->assertCount(1, $children);
        $this->assertSame('Обувь', $children->first()->name);
    }

    public function test_get_descendants(): void
    {
        $root = Category::query()->where('name', 'Электроника')->firstOrFail();

        $descendants = $this->repository->getDescendants($root);

        $this->assertCount(2, $descendants);
        $this->assertTrue($descendants->contains('name', 'Смартфоны'));
        $this->assertTrue($descendants->contains('name', 'Ноутбуки'));
    }

    public function test_get_ancestors(): void
    {
        $leaf = Category::query()->where('name', 'Смартфоны')->firstOrFail();

        $ancestors = $this->repository->getAncestors($leaf);

        $this->assertCount(1, $ancestors);
        $this->assertSame('Электроника', $ancestors->first()->name);
    }

    public function test_get_ancestors_of_root_is_empty(): void
    {
        $root = Category::query()->where('name', 'Одежда')->firstOrFail();

        $this->assertCount(0, $this->repository->getAncestors($root));
    }
}
