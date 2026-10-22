<?php

namespace App\Models;

use Database\Factories\PropertyCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'name_ar', 'slug', 'icon', 'sort_order', 'is_active'])]
class PropertyCategory extends Model
{
    /** @use HasFactory<PropertyCategoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'category_id');
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }
}
