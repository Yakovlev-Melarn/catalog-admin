<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $attribute_id
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Attribute $attribute
 */
#[Fillable(['product_id', 'attribute_id', 'value'])]
class ProductAttribute extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
