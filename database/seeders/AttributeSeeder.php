<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $attributes = [
            'brand' => ['name' => 'Бренд', 'type' => 'text', 'is_required' => true],
            'color' => ['name' => 'Цвет', 'type' => 'text', 'is_required' => false],
            'size' => ['name' => 'Размер', 'type' => 'text', 'is_required' => false],
            'weight' => ['name' => 'Вес, кг', 'type' => 'number', 'is_required' => false],
            'material' => ['name' => 'Материал', 'type' => 'text', 'is_required' => false],
            'screen_size' => ['name' => 'Диагональ экрана, дюймы', 'type' => 'number', 'is_required' => false],
            'storage' => ['name' => 'Память, ГБ', 'type' => 'number', 'is_required' => false],
            'battery' => ['name' => 'Батарея, мАч', 'type' => 'number', 'is_required' => false],
            'warranty' => ['name' => 'Гарантия', 'type' => 'select', 'is_required' => false],
            'in_stock' => ['name' => 'В наличии', 'type' => 'boolean', 'is_required' => false],
        ];

        foreach ($attributes as $data) {
            Attribute::query()->firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
