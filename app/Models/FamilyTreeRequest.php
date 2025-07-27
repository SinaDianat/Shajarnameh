<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyTreeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'status' => 'string',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}