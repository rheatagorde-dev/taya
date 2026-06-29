<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region',
        'address',
        'capacity',
    ];

    public function detainees(): HasMany
    {
        return $this->hasMany(Detainee::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
