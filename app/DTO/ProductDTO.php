<?php

namespace App\DTO;

class ProductDTO
{
    /**
     * @param  array<int, array{attribute_id: int, value: string|null}>  $attributes
     */
    public function __construct(
        public string $name,
        public ?int $categoryId = null,
        public ?string $description = null,
        public string $price = '0.00',
        public bool $isActive = true,
        /** @var array<string> */
        public array $images = [],
        /** @var array<int, array{attribute_id: int, value: string|null}> */
        public array $attributes = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $categoryId = $data['category_id'] ?? null;
        $description = $data['description'] ?? null;

        return new static(
            name: (string) ($data['name'] ?? ''),
            categoryId: $categoryId !== null ? (int) $categoryId : null,
            description: $description !== null ? (string) $description : null,
            price: (string) ($data['price'] ?? '0.00'),
            isActive: (bool) ($data['is_active'] ?? true),
            images: array_values((array) ($data['images'] ?? [])),
            attributes: (array) ($data['attributes'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'price' => $this->price,
            'is_active' => $this->isActive,
            'images' => $this->images,
        ];
    }
}
