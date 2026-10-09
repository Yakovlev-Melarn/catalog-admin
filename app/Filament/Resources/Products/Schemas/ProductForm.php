<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                FileUpload::make('images')
                    ->label('Изображения')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->maxSize(4096)
                    ->disk('public')
                    ->directory('products')
                    ->saveUploadedFileUsing(static function (TemporaryUploadedFile $file): ?string {
                        if (! $file->exists()) {
                            return null;
                        }

                        $path = $file->storeAs(
                            'products',
                            Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg'),
                            [
                                'disk' => 'public',
                                'mimetype' => $file->getMimeType(),
                            ],
                        );

                        $disk = Storage::disk('public');
                        $manager = new ImageManager(new Driver);
                        $image = $manager->decodePath($disk->path($path));
                        $image->scaleDown(width: 1920, height: 1080);
                        $image->save($disk->path($path), quality: 85);

                        return $path;
                    })
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₽'),
                TextInput::make('stock')
                    ->label('Остаток')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
