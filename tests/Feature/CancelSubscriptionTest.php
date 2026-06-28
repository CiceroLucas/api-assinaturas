<?php

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Student;
use App\Models\Subscription;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('schedules an active subscription cancellation for the end of the billing period', function () {
    Carbon::setTestNow('2026-06-28 10:00:00');

    $student = Student::create([
        'name' => 'Lucas',
        'email' => 'lucas@example.com',
    ]);

    $plan = Plan::create([
        'name' => 'Mensal',
        'price' => 49.90,
        'billing_cycle_in_days' => 30,
    ]);

    $subscription = Subscription::create([
        'student_id' => $student->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'next_billing_date' => Carbon::today()->addDays(30),
    ]);

    $response = $this->postJson("/api/subscriptions/{$subscription->id}/cancel");

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Sua assinatura foi cancelada com sucesso.',
            'status' => 'active_until_end_of_period',
        ]);

    $subscription->refresh();

    expect($subscription->status)->toBe(SubscriptionStatus::ACTIVE)
        ->and($subscription->cancelled_at)->not->toBeNull();
});

it('does not allow cancelling the same subscription twice', function () {
    Carbon::setTestNow('2026-06-28 10:00:00');

    $student = Student::create([
        'name' => 'Lucas',
        'email' => 'lucas.duplicado@example.com',
    ]);

    $plan = Plan::create([
        'name' => 'Mensal',
        'price' => 49.90,
        'billing_cycle_in_days' => 30,
    ]);

    $subscription = Subscription::create([
        'student_id' => $student->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::ACTIVE,
        'next_billing_date' => Carbon::today()->addDays(30),
        'cancelled_at' => Carbon::now(),
    ]);

    $this->postJson("/api/subscriptions/{$subscription->id}/cancel")
        ->assertStatus(422)
        ->assertJson([
            'message' => 'Esta assinatura ja foi cancelada anteriormente.',
            'errors' => [
                'subscription' => [
                    'Esta assinatura ja foi cancelada anteriormente.',
                ],
            ],
        ]);
});
