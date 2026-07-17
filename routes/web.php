<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PeopleApiController;
use Illuminate\Support\Facades\Route;

// Public Calendar View
Route::get('/', [CalendarController::class, 'index'])->name('calendar');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Fallback GET logout for ease of use

// Autocomplete API (Public search or auth search)
Route::get('/api/people/search', [PeopleApiController::class, 'search'])->name('api.people.search');

// Activity CRUD (Protected by auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/activities', [CalendarController::class, 'store'])->name('activities.store');
    Route::post('/activities/{id}', [CalendarController::class, 'update'])->name('activities.update');
    Route::post('/activities/{id}/delete', [CalendarController::class, 'destroy'])->name('activities.destroy');
    
    // Admin Analytics Dashboard (Requires admin check inside controller or middleware)
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/categories', [AdminController::class, 'addCategory'])->name('admin.categories.store');
    Route::post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'])->name('admin.categories.destroy');
    Route::post('/admin/users', [AdminController::class, 'addUser'])->name('admin.users.store');
    Route::post('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
    Route::get('/admin/activities/export', [AdminController::class, 'exportExcel'])->name('admin.activities.export');
});
