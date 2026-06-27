<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required', 
            'price' => 'required|numeric', 
            'billing_cycle_in_days' => 'required|integer'
        ]);
        return response()->json(\App\Models\Plan::create($data), 201);
    }
}
