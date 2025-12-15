<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Homepage - Student Exam Code Entry
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/captcha/refresh', [LoginController::class, 'refreshCaptchaAjax'])->name('captcha.refresh');

// Admin Dashboard (placeholder - will be protected by auth middleware)
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard')->middleware('auth');

// Admin API Routes
Route::middleware('auth')->prefix('admin/api')->name('admin.api.')->group(function () {
    // Classroom Management
    Route::apiResource('classrooms', \App\Http\Controllers\Admin\ClassroomController::class);
    Route::post('classrooms/{classroom}/toggle-status', [\App\Http\Controllers\Admin\ClassroomController::class, 'toggleStatus'])
        ->name('classrooms.toggle-status');
});


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
