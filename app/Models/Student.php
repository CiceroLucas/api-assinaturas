<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['name', 'email'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}