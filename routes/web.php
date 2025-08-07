<?php

use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
use App\Http\Controllers\Admin\StatusController;    
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Estimator\ProjectController as EstimatorProjectController;
use App\Http\Controllers\Admin\GcController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/remarks', [ProjectController::class, 'addRemark'])->name('projects.remarks.store');
});

Route::middleware(['auth', 'role:admin,bid_coordinator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('proposals', AdminProposalController::class);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('statuses', StatusController::class);
    Route::post('statuses/update-order', [StatusController::class, 'updateOrder'])->name('statuses.update-order');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('types', TypeController::class);
    Route::post('types/update-order', [TypeController::class, 'updateOrder'])->name('types.update-order');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('gcs', GcController::class);
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
});

// Estimator Routes
Route::middleware(['auth', 'role:estimator,head_estimator'])->prefix('estimator')->name('estimator.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [EstimatorProjectController::class, 'dashboard'])->name('dashboard');
    
    // Projects
    Route::get('/projects', [EstimatorProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [EstimatorProjectController::class, 'show'])->name('projects.show');
    
    // Status Update
    Route::patch('/projects/{project}/status', [EstimatorProjectController::class, 'updateStatus'])->name('projects.status');
    
    // Remarks
    Route::post('/projects/{project}/remarks', [EstimatorProjectController::class, 'storeRemark'])->name('projects.remarks.store');
    Route::delete('/remarks/{remark}', [EstimatorProjectController::class, 'deleteRemark'])->name('projects.remarks.destroy');
});
require __DIR__.'/auth.php';
