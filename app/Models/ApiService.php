<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiService extends Model
{
    protected $fillable = ['name'];

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function allowedApiTokenTypes(): BelongsToMany
    {
        return $this->belongsToMany(TokenType::class, 'api_service_token_types');
    }
}
