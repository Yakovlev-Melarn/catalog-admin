<?php

namespace App\DTO;

class CategoryDTO
{
    public function __construct(
        public string $name,
        public ?int $parentId = null,
        public int $sortOrder = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $parentId = $data['parent_id'] ?? null;

        return new static(
            name: (string) ($data['name'] ?? ''),
            parentId: $parentId !== null ? (int) $parentId : null,
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
        ];
    }
}
