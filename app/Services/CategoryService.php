<?php

namespace App\Services;

use App\DTO\CategoryDTO;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class CategoryService
{
    public function __construct(
        private CategoryRepository $categories,
    ) {}

    /**
     * @return Collection<int, Category>
     */
    public function tree(): Collection
    {
        return $this->categories->getTree();
    }

    /**
     * @throws ValidationException
     */
    public function create(CategoryDTO $dto): Category
    {
        $data = $this->validate($dto);

        return $this->categories->create($data);
    }

    /**
     * @throws ValidationException
     */
    public function update(Category $category, CategoryDTO $dto): Category
    {
        $data = $this->validate($dto);

        if ($data['parent_id'] === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'Категория не может быть вложена сама в себя.',
            ]);
        }

        if ($data['parent_id'] !== null) {
            $descendants = $this->categories->getDescendants($category);

            if ($descendants->contains('id', $data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Категория не может быть вложена в собственную подкатегорию.',
                ]);
            }
        }

        return $this->categories->update($category, $data);
    }

    /**
     * @throws ValidationException
     */
    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Нельзя удалить категорию: сначала удалите или перенесите подкатегории.',
            ]);
        }

        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Нельзя удалить категорию: в ней есть товары.',
            ]);
        }

        $this->categories->delete($category);
    }

    /**
     * @throws ValidationException
     */
    public function validate(CategoryDTO $dto): array
    {
        $data = $dto->toArray();

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['integer', 'min:0'],
        ])->validate();

        return $data;
    }
}
