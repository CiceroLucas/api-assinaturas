<?php

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Validation\ValidationException;

class CancelSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::CANCELED || $subscription->cancelled_at !== null) {
            throw ValidationException::withMessages([
                'subscription' => 'Esta assinatura ja foi cancelada anteriormente.',
            ]);
        }

        $subscription->update([
            'cancelled_at' => now(),
        ]);

        return $subscription;
    }
}
