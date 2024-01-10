<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use  HasFactory;

    protected $guarded = ['id'];

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(Test::class)->using(ProductTest::class);
    }

    public function productTests(): HasMany
    {
        return $this->hasMany(ProductTest::class);
    }
}
