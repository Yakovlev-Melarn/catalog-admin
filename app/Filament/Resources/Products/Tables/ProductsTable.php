<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\ProductAttribute;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchable(['name', 'sku'])
            ->columns([
                ImageColumn::make('images')
                    ->label('Изображение')
                    ->disk('public')
                    ->limit(2),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('category.name')
                    ->label('Категория')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('RUB')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Остаток')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(fn (): array => self::categoryOptions())
                    ->query(function ($query, array $data) {
                        $categoryId = $data['value'] ?? null;

                        if (filled($categoryId)) {
                            $query->whereIn('category_id', self::categoryWithDescendantIds((int) $categoryId));
                        }

                        return $query;
                    }),
                SelectFilter::make('attribute')
                    ->label('Атрибут')
                    ->options(fn (): array => Attribute::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->query(function ($query, array $data) {
                        $attributeId = $data['value'] ?? null;

                        if (filled($attributeId)) {
                            $query->whereHas('attributes', function ($relation) use ($attributeId): void {
                                $relation->where('attribute_id', (int) $attributeId);
                            });
                        }

                        return $query;
                    }),
                SelectFilter::make('attribute_value')
                    ->label('Значение атрибута')
                    ->visible(fn ($livewire): bool => filled(self::filterValue($livewire->tableFilters['attribute'] ?? null)))
                    ->options(fn ($livewire): array => self::attributeValueOptions((int) self::filterValue($livewire->tableFilters['attribute'] ?? null)))
                    ->query(function ($query, array $data, $livewire) {
                        $attributeId = self::filterValue($livewire->tableFilters['attribute'] ?? null);
                        $value = $data['value'] ?? null;

                        if (filled($attributeId) && filled($value)) {
                            $query->whereHas('attributes', function ($relation) use ($attributeId, $value): void {
                                $relation->where('attribute_id', (int) $attributeId)
                                    ->where('value', $value);
                            });
                        }

                        return $query;
                    }),
                TernaryFilter::make('is_active')
                    ->label('Активен'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function filterValue(mixed $state): mixed
    {
        if (is_array($state)) {
            return $state['value'] ?? null;
        }

        return $state;
    }

    /**
     * Categories as grouped options: root name => [id => name, child id => "— child name", ...].
     *
     * @return array<string | array<string>>
     */
    private static function categoryOptions(): array
    {
        /** @var array<int, Category> $categories */
        $categories = Category::query()->orderBy('sort_order')->orderBy('id')->get()->all();

        $options = [];

        foreach ($categories as $root) {
            if ($root->parent_id !== null) {
                continue;
            }

            $descendants = [];
            self::collectDescendants($categories, $root->id, 1, $descendants);

            if ($descendants === []) {
                $options[$root->id] = $root->name;

                continue;
            }

            $options[$root->name] = [$root->id => $root->name, ...$descendants];
        }

        return $options;
    }

    /**
     * @param  array<int, Category>  $categories
     * @param  array<int, string>  $descendants
     */
    private static function collectDescendants(array $categories, int $parentId, int $depth, array &$descendants): void
    {
        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $descendants[$category->id] = str_repeat('— ', $depth).' '.$category->name;
                self::collectDescendants($categories, $category->id, $depth + 1, $descendants);
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private static function categoryWithDescendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $parents = [$categoryId];

        while ($parents !== []) {
            $childIds = Category::query()->where('parent_id', $parents)->pluck('id')->all();
            $ids = array_merge($ids, $childIds);
            $parents = $childIds;
        }

        return $ids;
    }

    /**
     * @return array<string, string>
     */
    private static function attributeValueOptions(int $attributeId): array
    {
        $attribute = Attribute::query()->find($attributeId);

        $values = ($attribute !== null && filled($attribute->values))
            ? $attribute->values
            : ProductAttribute::query()
                ->where('attribute_id', $attributeId)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->distinct()
                ->pluck('value')
                ->all();

        return collect($values)->mapWithKeys(fn (string $value): array => [$value => $value])->all();
    }
}
