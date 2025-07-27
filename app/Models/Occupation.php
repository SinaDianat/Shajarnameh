<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    protected $table = 'occupations';

    protected $fillable = [
        'name',
        'description',
    ];

    public function people()
    {
        return $this->hasMany(People::class, 'occupation_id');
    }
}