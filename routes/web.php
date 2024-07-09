<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TAController;

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
// Display the form to create a new request
Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
// Handle the form submission to store a new request
Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
Route::get('/requests/view', [RequestController::class, 'index'])->name('requests.view');

Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
});


// TA routes
Route::middleware(['auth', 'role:ta'])->group(function () {
Route::get('/ta/requests', [TAController::class, 'index'])->name('ta.index');
Route::get('/ta/requests/{id}', [TAController::class, 'show'])->name('ta.show');
//Route::post('/ta/requests/{id}', [TAController::class, 'update'])->name('ta.update');
Route::put('/ta/requests/{id}', [TAController::class, 'update'])->name('ta.update'); 
});


require __DIR__.'/auth.php';