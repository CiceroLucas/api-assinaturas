<?php

namespace App\Actions\Subscriptions;

use App\Models\Plan;
use App\Models\Student;
use App\Models\Subscription;
use App\Enums\SubscriptionStatus;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateSubscriptionAction
{
    public function execute(Student $student, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($student, $plan) {
            
            $subscription = Subscription::create([
                'student_id' => $student->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::ACTIVE,
                'next_billing_date' => Carbon::now()->addDays($plan->billing_cycle_in_days),
            ]);

            $subscription->invoices()->create([
                'amount' => $plan->price,
                'status' => InvoiceStatus::PENDING,
                'due_date' => Carbon::now(),
            ]);

            return $subscription;
        });
    }
}