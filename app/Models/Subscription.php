<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\SubscriptionStatus;

class Subscription extends Model
{
    protected $fillable = ['student_id', 'plan_id', 'status', 'next_billing_date'];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'next_billing_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}