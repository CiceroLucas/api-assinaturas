<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Actions\Subscriptions\CreateSubscriptionAction;
use App\Models\Student;
use App\Models\Plan;
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
}