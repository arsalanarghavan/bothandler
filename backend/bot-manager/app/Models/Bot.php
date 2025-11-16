<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'github_repo_url',
        'github_branch',
        'domain',
        'environment',
        'service_type',
        'deploy_command',
        'status',
        'last_deployed_at',
    ];

    protected $casts = [
        'environment' => 'array',
        'last_deployed_at' => 'datetime',
    ];

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }
}


