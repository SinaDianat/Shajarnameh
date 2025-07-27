<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    protected $fillable = [
        'name',
        'gender',
        'city_of_birth',
        'birthday',
        'city_of_life',
        'occupation_id',
        'description',
        'father_id',
        'mother_id',
        'children_ids',
        'partners_ids',
        'city_of_die',
        'date_of_die',
    ];

    protected $casts = [
        'children_ids' => 'array',
        'partners_ids' => 'array',
    ];

    public function cityOfBirth()
    {
        return $this->belongsTo(City::class, 'city_of_birth');
    }

    public function cityOfLife()
    {
        return $this->belongsTo(City::class, 'city_of_life');
    }

    public function cityOfDie()
    {
        return $this->belongsTo(City::class, 'city_of_die');
    }

    public function occupation()
    {
        return $this->belongsTo(Occupation::class, 'occupation_id');
    }

    public function father()
    {
        return $this->belongsTo(People::class, 'father_id');
    }

    public function mother()
    {
        return $this->belongsTo(People::class, 'mother_id');
    }

    public function children()
    {
        return $this->hasMany(People::class, 'id')->whereIn('id', $this->children_ids ?? []);
    }

    public function partners()
    {
        return $this->hasMany(People::class, 'id')->whereIn('id', $this->partners_ids ?? []);
    }
}
