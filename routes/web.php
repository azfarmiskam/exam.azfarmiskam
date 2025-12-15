<?php

use Illuminate\Support\Facades\Route;

// Homepage - Student Exam Code Entry
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Temporary Login Route (will be replaced with auth system)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Exam Code Verification
Route::post('/exam/verify', function () {
    $code = strtoupper(request('code'));
    
    // Check if classroom exists with this code
    $classroom = \App\Models\Classroom::where('code', $code)
        ->where('is_active', true)
        ->first();
    
    if (!$classroom) {
        return redirect()->back()->with('error', 'Invalid exam code. Please check and try again.');
    }
    
    // Redirect to student registration for this classroom
    return redirect()->route('exam.register', ['code' => $code]);
})->name('exam.verify');

// Student Registration (placeholder for now)
Route::get('/exam/{code}/register', function ($code) {
    $classroom = \App\Models\Classroom::where('code', $code)
        ->where('is_active', true)
        ->firstOrFail();
    
    return view('exam.register', compact('classroom'));
})->name('exam.register');
