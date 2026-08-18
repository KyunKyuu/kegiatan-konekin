<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PeopleApiController;
use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

// Public Calendar View
Route::get('/', [CalendarController::class, 'index'])->name('calendar');

// Monitoring Module View (Public view, protected actions)
Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
Route::get('/monitoring/people/{id}', [MonitoringController::class, 'showPersonDetail'])->name('monitoring.people.show');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Fallback GET logout for ease of use

// Autocomplete API & Person Detail (Public GET search or auth search)
Route::get('/api/people/search', [PeopleApiController::class, 'search'])->name('api.people.search');
Route::get('/api/people/{id}/detail', [MonitoringController::class, 'getPersonDetail'])->name('api.people.detail');

// Activity & Milestone CRUD (Protected by auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/activities', [CalendarController::class, 'store'])->name('activities.store');
    Route::post('/activities/{id}', [CalendarController::class, 'update'])->name('activities.update');
    Route::post('/activities/{id}/delete', [CalendarController::class, 'destroy'])->name('activities.destroy');
    
    // Monitoring Milestones & Person Profile CRUD
    Route::post('/monitoring/milestones', [MonitoringController::class, 'store'])->name('monitoring.milestones.store');
    Route::post('/monitoring/milestones/{id}', [MonitoringController::class, 'update'])->name('monitoring.milestones.update');
    Route::post('/monitoring/milestones/{id}/delete', [MonitoringController::class, 'destroy'])->name('monitoring.milestones.destroy');
    Route::post('/monitoring/milestones/{id}/assign-people', [MonitoringController::class, 'assignPeople'])->name('monitoring.milestones.assign_people');
    Route::post('/monitoring/people/{id}/profile', [MonitoringController::class, 'updatePersonProfile'])->name('monitoring.people.profile.update');
    
    // Target Checklist & Master Targets
    Route::post('/monitoring/targets/{id}/toggle', [MonitoringController::class, 'togglePersonTarget'])->name('monitoring.targets.toggle');
    Route::post('/monitoring/people/{id}/targets', [MonitoringController::class, 'assignMasterTarget'])->name('monitoring.people.targets.assign');
    Route::post('/monitoring/master-targets', [MonitoringController::class, 'storeMasterTarget'])->name('monitoring.master_targets.store');
    Route::post('/monitoring/master-targets/{id}/delete', [MonitoringController::class, 'destroyMasterTarget'])->name('monitoring.master_targets.destroy');

    // Multi Notes
    Route::post('/monitoring/people/{id}/notes', [MonitoringController::class, 'addPersonNote'])->name('monitoring.people.notes.store');
    Route::post('/monitoring/notes/{id}/delete', [MonitoringController::class, 'deletePersonNote'])->name('monitoring.notes.destroy');



    // Admin Analytics Dashboard (Requires admin check inside controller or middleware)
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/categories', [AdminController::class, 'addCategory'])->name('admin.categories.store');
    Route::post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'])->name('admin.categories.destroy');
    Route::post('/admin/users', [AdminController::class, 'addUser'])->name('admin.users.store');
    Route::post('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
    Route::get('/admin/activities/export', [AdminController::class, 'exportExcel'])->name('admin.activities.export');
});

