<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExperimentGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function experiments()
    {
        return $this->hasMany(Experiment::class);
    }
}
