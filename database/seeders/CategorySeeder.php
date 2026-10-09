<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $electronics = Category::query()->firstOrCreate(
            ['name' => 'Электроника'],
            ['parent_id' => null, 'sort_order' => 1],
        );

        Category::query()->firstOrCreate(
            ['name' => 'Смартфоны'],
            ['parent_id' => $electronics->id, 'sort_order' => 1],
        );

        Category::query()->firstOrCreate(
            ['name' => 'Ноутбуки'],
            ['parent_id' => $electronics->id, 'sort_order' => 2],
        );

        $clothing = Category::query()->firstOrCreate(
            ['name' => 'Одежда'],
            ['parent_id' => null, 'sort_order' => 2],
        );

        Category::query()->firstOrCreate(
            ['name' => 'Обувь'],
            ['parent_id' => $clothing->id, 'sort_order' => 1],
        );
    }
}
