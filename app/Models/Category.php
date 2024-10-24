<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $table = 'category';

    protected $guarded = [];
//    protected function id(): Attribute
//    {
//        return Attribute::make(
//            get: fn (mixed $value, array $attributes) => $attributes['category_id'],
//            set: fn (mixed $value, array $attributes) => $attributes['category_id'],
//        );
//    }
    public function products()
    {
        return $this->hasMany(
            Product::class,
            'main_category_id',
            'id'
        );
    }

    public function marketing_products()
    {
        return $this->hasMany(
            Product::class,
            'marketing_category_id',
            'id'
        );
    }
}
