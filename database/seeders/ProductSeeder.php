<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([CategorySeeder::class, AttributeSeeder::class]);

        $category = fn (string $name) => Category::query()->where('name', $name)->firstOrFail();

        $attribute = fn (string $name) => Attribute::query()->where('name', $name)->firstOrFail();

        $products = [
            // Смартфоны
            ['name' => 'iPhone 15 Pro', 'category' => 'Смартфоны', 'price' => '129990.00', 'attributes' => [
                'Бренд' => 'Apple', 'Цвет' => 'Естественный титан', 'Память, ГБ' => 256, 'В наличии' => true,
            ]],
            ['name' => 'Samsung Galaxy S24 Ultra', 'category' => 'Смартфоны', 'price' => '119990.00', 'attributes' => [
                'Бренд' => 'Samsung', 'Цвет' => 'Титановый серый', 'Память, ГБ' => 512, 'В наличии' => true,
            ]],
            ['name' => 'Xiaomi 14', 'category' => 'Смартфоны', 'price' => '69990.00', 'attributes' => [
                'Бренд' => 'Xiaomi', 'Цвет' => 'Чёрный', 'Память, ГБ' => 256, 'В наличии' => true,
            ]],
            ['name' => 'Google Pixel 8 Pro', 'category' => 'Смартфоны', 'price' => '79990.00', 'attributes' => [
                'Бренд' => 'Google', 'Цвет' => 'Океан', 'Память, ГБ' => 128, 'В наличии' => false,
            ]],
            ['name' => 'OnePlus 12', 'category' => 'Смартфоны', 'price' => '74990.00', 'attributes' => [
                'Бренд' => 'OnePlus', 'Цвет' => 'Изумрудный', 'Память, ГБ' => 256, 'В наличии' => true,
            ]],
            // Ноутбуки
            ['name' => 'MacBook Pro 14 (M3)', 'category' => 'Ноутбуки', 'price' => '169990.00', 'attributes' => [
                'Бренд' => 'Apple', 'Вес, кг' => 1.6, 'Диагональ экрана, дюймы' => 14, 'В наличии' => true,
            ]],
            ['name' => 'ASUS ROG Zephyrus G14', 'category' => 'Ноутбуки', 'price' => '149990.00', 'attributes' => [
                'Бренд' => 'ASUS', 'Вес, кг' => 1.7, 'Диагональ экрана, дюймы' => 14, 'В наличии' => true,
            ]],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'category' => 'Ноутбуки', 'price' => '159990.00', 'attributes' => [
                'Бренд' => 'Lenovo', 'Вес, кг' => 1.1, 'Диагональ экрана, дюймы' => 14, 'В наличии' => false,
            ]],
            ['name' => 'Dell XPS 13', 'category' => 'Ноутбуки', 'price' => '129990.00', 'attributes' => [
                'Бренд' => 'Dell', 'Вес, кг' => 1.2, 'Диагональ экрана, дюймы' => 13.4, 'В наличии' => true,
            ]],
            ['name' => 'HP Spectre x360', 'category' => 'Ноутбуки', 'price' => '119990.00', 'attributes' => [
                'Бренд' => 'HP', 'Вес, кг' => 1.4, 'Диагональ экрана, дюймы' => 13.5, 'В наличии' => true,
            ]],
            // Обувь
            ['name' => 'Nike Air Max 270', 'category' => 'Обувь', 'price' => '14990.00', 'attributes' => [
                'Бренд' => 'Nike', 'Цвет' => 'Бело-чёрный', 'Размер' => 42, 'В наличии' => true,
            ]],
            ['name' => 'Adidas Ultraboost 22', 'category' => 'Обувь', 'price' => '16990.00', 'attributes' => [
                'Бренд' => 'Adidas', 'Цвет' => 'Чёрно-белый', 'Размер' => 41, 'В наличии' => true,
            ]],
            ['name' => 'New Balance 990v6', 'category' => 'Обувь', 'price' => '21990.00', 'attributes' => [
                'Бренд' => 'New Balance', 'Цвет' => 'Серый', 'Размер' => 43, 'В наличии' => false,
            ]],
            ['name' => 'Puma RS-X', 'category' => 'Обувь', 'price' => '12990.00', 'attributes' => [
                'Бренд' => 'Puma', 'Цвет' => 'Синий', 'Размер' => 42, 'В наличии' => true,
            ]],
            ['name' => 'Reebok Classic Leather', 'category' => 'Обувь', 'price' => '9990.00', 'attributes' => [
                'Бренд' => 'Reebok', 'Цвет' => 'Белый', 'Размер' => 40, 'В наличии' => false,
            ]],
            // Электроника
            ['name' => 'Apple Watch Series 9', 'category' => 'Электроника', 'price' => '39990.00', 'attributes' => [
                'Бренд' => 'Apple', 'Цвет' => 'Полночь', 'Гарантия' => '1 год',
            ]],
            ['name' => 'AirPods Pro 2', 'category' => 'Электроника', 'price' => '27990.00', 'attributes' => [
                'Бренд' => 'Apple', 'Гарантия' => '1 год', 'В наличии' => true,
            ]],
            ['name' => 'Sony WH-1000XM5', 'category' => 'Электроника', 'price' => '34990.00', 'attributes' => [
                'Бренд' => 'Sony', 'Цвет' => 'Чёрный', 'Батарея, мАч' => 3300,
            ]],
            // Одежда
            ['name' => 'Футболка мужская хлопковая', 'category' => 'Одежда', 'price' => '1990.00', 'attributes' => [
                'Бренд' => 'Zara', 'Цвет' => 'Белый', 'Размер' => 'L', 'Материал' => 'Хлопок',
            ]],
            ['name' => 'Куртка джинсовая женская', 'category' => 'Одежда', 'price' => '5990.00', 'attributes' => [
                'Бренд' => 'Uniqlo', 'Цвет' => 'Синий', 'Размер' => 'M', 'Материал' => 'Деним',
            ]],
        ];

        foreach ($products as $data) {
            $product = Product::query()->firstOrCreate(
                ['name' => $data['name']],
                [
                    'category_id' => $category($data['category'])->id,
                    'description' => null,
                    'price' => $data['price'],
                    'is_active' => true,
                ],
            );

            $pivot = [];
            foreach ($data['attributes'] as $name => $value) {
                $pivot[$attribute($name)->id] = ['value' => (string) $value];
            }

            $product->attributes()->sync($pivot);
        }
    }
}
