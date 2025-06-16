<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TokenType extends Model
{
    protected $fillable = ['name'];

    public function apiTokens() : HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
