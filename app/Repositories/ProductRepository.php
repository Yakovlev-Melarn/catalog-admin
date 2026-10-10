<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ProductRepository
{
    public function find(int $id): ?Product
    {
        /** @var Product|null $product */
        $product = Product::query()->find($id);

        return $product;
    }

    public function findOrFail(int $id): Product
    {
        /** @var Product $product */
        $product = Product::query()->findOrFail($id);

        return $product;
    }

    /**
     * Filter by category_id, active, search, attributes (attribute_id => value).
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Product>
     */
    public function all(array $filters = []): Collection
    {
        $query = Product::query();
        $this->applyFilters($query, $filters);

        return $query
            ->orderBy('id')
            ->get();
    }

    /**
     * Filter by category_id, active, search, attributes (attribute_id => value).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Product::query();
        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $attributes = $data['attributes'] ?? [];
        unset($data['attributes']);

        /** @var Product $product */
        $product = Product::query()->create($data);

        $this->syncAttributes($product, $attributes);

        return $product;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $attributes = $data['attributes'] ?? null;
        unset($data['attributes']);

        $previousImages = $this->imagesOf($product);

        $product->update($data);

        if ($attributes !== null) {
            $this->syncAttributes($product, $attributes);
        }

        $removed = array_diff($previousImages, $this->imagesOf($product));

        if ($removed !== []) {
            $this->deleteImageFiles(array_values($removed));
        }

        return $product;
    }

    public function delete(Product $product): void
    {
        $images = $this->imagesOf($product);

        $product->delete();

        if ($images !== []) {
            $this->deleteImageFiles($images);
        }
    }

    /**
     * @param  array<int, array{attribute_id: int, value: string|null}>  $attributes
     */
    public function syncAttributes(Product $product, array $attributes): void
    {
        $pivot = [];

        foreach ($attributes as $item) {
            $pivot[(int) $item['attribute_id']] = ['value' => (string) $item['value']];
        }

        $product->attributes()->sync($pivot);
    }

    /**
     * @return array<string>
     */
    private function imagesOf(Product $product): array
    {
        $images = $product->images;

        return is_array($images) ? $images : [];
    }

    /**
     * @param  array<string>  $paths
     */
    private function deleteImageFiles(array $paths): void
    {
        $disk = Storage::disk('public');

        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }

    /**
     * Applies the category, active, search and attribute filters to the query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters($query, array $filters): void
    {
        $categoryId = $filters['category_id'] ?? null;
        $active = $filters['active'] ?? null;
        $search = $filters['search'] ?? null;
        $attributes = $filters['attributes'] ?? [];

        $query
            ->when($categoryId !== null, fn ($q) => $q->where('category_id', $categoryId))
            ->when($active !== null, fn ($q) => $q->where('is_active', $active))
            ->when(filled($search), fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->when($attributes !== [], function ($q) use ($attributes) {
                foreach ($attributes as $attributeId => $value) {
                    $q->whereHas('attributes', function ($relation) use ($attributeId, $value): void {
                        $relation->where('attribute_id', $attributeId);

                        if ($value !== null && $value !== '') {
                            $relation->where('value', $value);
                        }
                    });
                }
            });
    }
}
