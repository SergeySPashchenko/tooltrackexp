<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'product';

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(
            Category::class,
            'main_category_id',
            'id'
        );
    }

    public function marketing_category()
    {
        return $this->belongsTo(
            Category::class,
            'marketing_category_id',
            'id'
        );
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function experiments()
    {
        return $this->hasMany(Experiment::class);
    }
}
