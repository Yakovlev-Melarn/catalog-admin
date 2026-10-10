<?php

namespace App\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Тип')
                    ->options([
                        'text' => 'Текст',
                        'number' => 'Число',
                        'select' => 'Выбор',
                        'boolean' => 'Да/Нет',
                    ])
                    ->required()
                    ->default('text'),
                KeyValue::make('values')
                    ->label('Значения')
                    ->visible(fn (Get $get): bool => $get('type') === 'select')
                    ->columnSpanFull()
                    ->helperText('Варианты для типа "Выбор"'),
                Toggle::make('is_required')
                    ->label('Обязательный')
                    ->default(false),
            ]);
    }
}
