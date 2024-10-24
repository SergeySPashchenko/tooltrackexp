<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gender extends Model
{
    use HasFactory;

    protected $table = 'gender';

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class, 'gender_id', 'gender_id');
    }
//    protected function id(): Attribute
//    {
//        return Attribute::make(
//            get: fn (mixed $value, array $attributes) => $attributes['gender_id'],
//        );
//    }
}
