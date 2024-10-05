<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property ProjectTest $projectTest
 */
class Test extends Model
{
    use  HasFactory;
    protected $guarded = ['id'];

    public function testProducts(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using(ProductTest::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->using(ProjectTest::class)->as('projectTest')->withTrashed();
    }


}
