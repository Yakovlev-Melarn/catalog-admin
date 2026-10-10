<?php

namespace App\DTO;

class AttributeDTO
{
    /**
     * @param  array<string>  $values
     */
    public function __construct(
        public string $name,
        public string $type = 'text',
        public bool $isRequired = false,
        public array $values = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            name: (string) ($data['name'] ?? ''),
            type: (string) ($data['type'] ?? 'text'),
            isRequired: (bool) ($data['is_required'] ?? false),
            values: array_values((array) ($data['values'] ?? [])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'is_required' => $this->isRequired,
            'values' => $this->values,
        ];
    }
}
