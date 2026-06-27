<?php

use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/plans', [PlanController::class, 'store']);
Route::post('/students', [StudentController::class, 'store']);
Route::post('/subscriptions', [SubscriptionController::class, 'store']);