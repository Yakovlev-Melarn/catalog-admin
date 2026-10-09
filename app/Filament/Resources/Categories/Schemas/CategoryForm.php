<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->options(static function (?Category $record): array {
                        $excluded = collect();

                        if ($record) {
                            $excluded->push($record->id);
                            $children = Category::query()->where('parent_id', $record->id)->pluck('id');

                            while ($children->isNotEmpty()) {
                                $excluded->merge($children);
                                $children = Category::query()->whereIn('parent_id', $children)->pluck('id');
                            }
                        }

                        return Category::query()
                            ->whereNotIn('id', $excluded)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Оставьте пустым для категории верхнего уровня'),
                TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }
}
