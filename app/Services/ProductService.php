<?php

namespace App\Services;

use App\DTO\ProductDTO;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class ProductService
{
    public function __construct(
        private ProductRepository $products,
    ) {}

    public function getById(int $id): Product
    {
        return $this->products->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Product>
     */
    public function list(array $filters = []): Collection
    {
        return $this->products->all($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->products->paginate($perPage, $filters);
    }

    /**
     * @throws ValidationException
     */
    public function create(ProductDTO $dto): Product
    {
        $data = $this->validate($dto);

        return DB::transaction(fn (): Product => $this->products->create($data));
    }

    /**
     * @throws ValidationException
     */
    public function update(Product $product, ProductDTO $dto): Product
    {
        $data = $this->validate($dto);

        return DB::transaction(fn (): Product => $this->products->update($product, $data));
    }

    public function delete(Product $product): void
    {
        $this->products->delete($product);
    }

    /**
     * @throws ValidationException
     */
    public function validate(ProductDTO $dto): array
    {
        $data = $dto->toArray();
        $data['attributes'] = $dto->attributes;

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active' => ['boolean'],
            'images' => ['array'],
            'images.*' => ['string'],
            'attributes' => ['array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'attributes.*.value' => ['nullable', 'string'],
        ])->validate();

        $attributeIds = collect($data['attributes'])->pluck('attribute_id');

        if ($attributeIds->unique()->count() !== $attributeIds->count()) {
            throw ValidationException::withMessages([
                'attributes' => 'Дублирующиеся атрибуты в данных товара.',
            ]);
        }

        return $data;
    }
}
