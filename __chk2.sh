#!/bin/bash
cd /mnt/d/dev
docker compose exec -T -w /srv/projects/catalog php sh -c 'php vendor/bin/pint 2>&1 | tail -4; echo ===; php vendor/bin/pint --test 2>&1 | tail -3'
echo "=== reseed:"
docker compose exec -T -w /srv/projects/catalog php php artisan db:seed --force 2>&1 | tail -3
echo "=== counts:"
docker compose exec -T -w /srv/projects/catalog php php artisan tinker --execute='echo "categories: " . App\Models\Category::query()->count() . PHP_EOL; echo "attributes: " . App\Models\Attribute::query()->count() . PHP_EOL; echo "products: " . App\Models\Product::query()->count() . PHP_EOL; echo "product_attributes: " . App\Models\ProductAttribute::query()->count() . PHP_EOL; echo "users: " . App\Models\User::query()->count() . PHP_EOL;'
rm -f /mnt/d/dev/projects/catalog/__chk.sh
