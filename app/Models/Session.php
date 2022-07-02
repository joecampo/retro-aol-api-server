<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;
    use HasApiTokens;

    protected $guarded = [];

    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }
}
