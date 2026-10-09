<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository
{
    public function find(int $id): ?Category
    {
        /** @var Category|null $category */
        $category = Category::query()->find($id);

        return $category;
    }

    public function findOrFail(int $id): Category
    {
        /** @var Category $category */
        $category = Category::query()->findOrFail($id);

        return $category;
    }

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        /** @var Category $category */
        $category = Category::query()->create($data);

        return $category;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * Nested tree of root categories with their children.
     *
     * @return Collection<int, Category>
     */
    public function getTree(): Collection
    {
        $categories = $this->all();

        return $this->buildTree($categories->all(), null);
    }

    /**
     * @return Collection<int, Category>
     */
    public function getChildren(int $parentId): Collection
    {
        return Category::query()
            ->where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * All descendants of the category (flat, breadth-first).
     *
     * @return Collection<int, Category>
     */
    public function getDescendants(Category $category): Collection
    {
        /** @var Collection<int, Category> $result */
        $result = new Collection;
        $parents = [$category->id];

        while ($parents !== []) {
            /** @var Collection<int, Category> $children */
            $children = Category::query()->whereIn('parent_id', $parents)->get();

            foreach ($children as $child) {
                $result->push($child);
            }

            $parents = $children->pluck('id')->all();
        }

        return $result;
    }

    /**
     * Ancestors from the direct parent up to the root.
     *
     * @return Collection<int, Category>
     */
    public function getAncestors(Category $category): Collection
    {
        /** @var Collection<int, Category> $ancestors */
        $ancestors = new Collection;
        $current = $category->parent;

        while ($current !== null) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * @param  array<int, int>  $sortOrders  category_id => sort_order
     */
    public function updateSortOrders(array $sortOrders): void
    {
        foreach ($sortOrders as $id => $sortOrder) {
            Category::query()->where('id', $id)->update(['sort_order' => $sortOrder]);
        }
    }

    /**
     * @param  array<int, Category>  $categories
     * @return Collection<int, Category>
     */
    private function buildTree(array $categories, ?int $parentId): Collection
    {
        $nodes = array_values(array_filter($categories, fn (Category $category): bool => $category->parent_id === $parentId));

        $tree = new Collection;

        foreach ($nodes as $node) {
            $node->setRelation('children', $this->buildTree($categories, $node->id));
            $tree->push($node);
        }

        return $tree;
    }
}
