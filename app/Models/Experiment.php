<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Experiment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function experimentVariations()
    {
        return $this->hasMany(ExperimentVariation::class);
    }

    public function experimentGroup()
    {
        return $this->belongsTo(ExperimentGroup::class);
    }

    public function product()
    {
        return $this->belongsToMany(Product::class);
    }
}
