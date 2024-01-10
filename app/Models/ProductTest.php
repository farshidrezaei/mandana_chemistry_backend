<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTest extends Pivot
{
    protected $guarded = ['id'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
