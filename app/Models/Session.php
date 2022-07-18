<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Session extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;

    protected $guarded = [];

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }
}
