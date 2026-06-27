<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate(['name' => 'required', 'email' => 'required|email|unique:students']);
        return response()->json(\App\Models\Student::create($data), 201);
    }
}
