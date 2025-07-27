<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'person_id',
        'access_ids',
        'is_admin', // اضافه شده
    ];

    protected $casts = [
        'access_ids' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    public function person()
    {
        return $this->belongsTo(People::class, 'person_id');
    }

    public function accessiblePeople()
    {
        return $this->hasMany(People::class, 'id', 'access_ids');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
      public function familyTreeRequests()
    {
        return $this->hasMany(FamilyTreeRequest::class, 'user_id');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}