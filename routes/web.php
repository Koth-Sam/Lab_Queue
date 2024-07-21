<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TAController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Student Routes
Route::middleware(['auth', 'role:student'])->group(function () {

    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/view', [RequestController::class, 'index'])->name('requests.view');
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
    Route::get('/home', [RequestController::class, 'studentHome'])->name('student.home');

});

// TA routes
Route::middleware(['auth', 'role:ta'])->group(function () {

    Route::get('/ta/requests', [TAController::class, 'index'])->name('ta.index');
    Route::get('/ta/requests/{id}', [TAController::class, 'show'])->name('ta.show');
    Route::post('/ta/requests/{id}', [TAController::class, 'update'])->name('ta.update');
    Route::put('/ta/requests/{id}', [TAController::class, 'update'])->name('ta.update');
    Route::get('/ta/dashboard', [TAController::class, 'dashboard'])->name('ta.dashboard'); 

});

//Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/requests', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/requests/{id}', [AdminController::class, 'show'])->name('admin.show');
    Route::put('/ta/requests/{id}', [TAController::class, 'update'])->name('admin.update');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    //Dashboard
    Route::get('/api/courses', [AdminController::class, 'getCourses'])->name('api.courses');
    Route::get('/api/requests-handled-by-ta', [AdminController::class, 'getRequestsHandledByTA'])->name('api.requests-handled-by-ta');


});

require __DIR__.'/auth.php';