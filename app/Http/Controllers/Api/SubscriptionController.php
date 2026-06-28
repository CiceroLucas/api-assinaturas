<?php
namespace App\Http\Controllers\Api;


use App\Actions\Subscriptions\CancelSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Actions\Subscriptions\CreateSubscriptionAction;
use App\Models\Student;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function store(
        StoreSubscriptionRequest $request, 
        CreateSubscriptionAction $createSubscriptionAction
    ): JsonResponse {
        
        $student = Student::findOrFail($request->validated('student_id'));
        $plan = Plan::findOrFail($request->validated('plan_id'));

        $subscription = $createSubscriptionAction->execute($student, $plan);

        return response()->json([
            'message' => 'Assinatura criada com sucesso.',
            'data' => $subscription->load('invoices')
        ], 201);
    }

    public function cancel(
        CancelSubscriptionRequest $request,
        Subscription $subscription,
        CancelSubscriptionAction $action
    ): JsonResponse {
        $subscription = $action->execute($subscription);

        return response()->json([
            'message' => 'Sua assinatura foi cancelada com sucesso.',
            'status' => 'active_until_end_of_period',
            'valid_until' => $subscription->next_billing_date,
        ], 200);
    }
}
