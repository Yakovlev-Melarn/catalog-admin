<?php

namespace App\Services;

use App\DTO\AttributeDTO;
use App\Models\Attribute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttributeService
{
    public const array TYPES = ['text', 'number', 'select', 'boolean'];

    public function getById(int $id): Attribute
    {
        /** @var Attribute $attribute */
        $attribute = Attribute::query()->findOrFail($id);

        return $attribute;
    }

    /**
     * @throws ValidationException
     */
    public function create(AttributeDTO $dto): Attribute
    {
        $data = $this->validate($dto);

        /** @var Attribute $attribute */
        $attribute = Attribute::query()->create($data);

        return $attribute;
    }

    /**
     * @throws ValidationException
     */
    public function update(Attribute $attribute, AttributeDTO $dto): Attribute
    {
        $data = $this->validate($dto, $attribute->id);

        $attribute->update($data);

        return $attribute;
    }

    /**
     * @throws ValidationException
     */
    public function delete(Attribute $attribute): void
    {
        if ($attribute->products()->exists()) {
            throw ValidationException::withMessages([
                'attribute' => 'Нельзя удалить характеристику: она используется в товарах.',
            ]);
        }

        $attribute->delete();
    }

    /**
     * @throws ValidationException
     */
    public function validate(AttributeDTO $dto, ?int $ignoreId = null): array
    {
        $data = $dto->toArray();

        $uniqueRule = 'unique:attributes,name';

        if ($ignoreId !== null) {
            $uniqueRule = $uniqueRule.','.$ignoreId.',id';
        }

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255', $uniqueRule],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
            'is_required' => ['boolean'],
            'values' => $dto->type === 'select' ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'values.*' => ['string'],
        ])->validate();

        return $data;
    }
}
