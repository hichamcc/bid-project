<?php

use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
use App\Http\Controllers\Admin\StatusController;    
use App\Http\Controllers\Admin\TypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AllocationController;
use App\Http\Controllers\Admin\EstimatorOffDayController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Estimator\AllocationController as EstimatorAllocationController;
use App\Http\Controllers\Estimator\ProjectController as EstimatorProjectController;
use App\Http\Controllers\Admin\GcController;
use App\Http\Controllers\Settings;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
})->name('home');

// Temporary email test route — remove after testing
Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::to('owenhicham@gmail.com')->send(new \App\Mail\TestMail());
        return 'Email sent successfully!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

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

Route::middleware(['auth', 'role:admin,bid_coordinator,head_estimator'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('progress', \App\Http\Controllers\Admin\ProgressController::class);
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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/workload', [\App\Http\Controllers\Admin\WorkloadController::class, 'index'])->name('workload.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/allocation', [AllocationController::class, 'index'])->name('allocation.index');
    Route::post('/allocation', [AllocationController::class, 'store'])->name('allocation.store');
    Route::get('/allocation/monthly', [AllocationController::class, 'monthly'])->name('allocation.monthly');
    Route::get('/allocation/{allocation}/edit', [AllocationController::class, 'edit'])->name('allocation.edit');
    Route::put('/allocation/{allocation}', [AllocationController::class, 'update'])->name('allocation.update');
    Route::delete('/allocation/{allocation}', [AllocationController::class, 'destroy'])->name('allocation.destroy');

    Route::get('/off-days', [EstimatorOffDayController::class, 'index'])->name('off-days.index');
    Route::post('/off-days', [EstimatorOffDayController::class, 'store'])->name('off-days.store');
    Route::delete('/off-days/{offDay}', [EstimatorOffDayController::class, 'destroy'])->name('off-days.destroy');
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
    
    // Workload
    Route::get('/workload', [EstimatorAllocationController::class, 'index'])->name('workload.index');
    Route::patch('/workload/{allocation}/status', [EstimatorAllocationController::class, 'updateStatus'])->name('workload.status');

    // Progress
    Route::get('/progress', [\App\Http\Controllers\Estimator\ProgressController::class, 'index'])->name('progress.index');
    Route::get('/progress/create', [\App\Http\Controllers\Estimator\ProgressController::class, 'create'])->name('progress.create');
    Route::post('/progress', [\App\Http\Controllers\Estimator\ProgressController::class, 'store'])->name('progress.store');
    Route::get('/progress/{progress}', [\App\Http\Controllers\Estimator\ProgressController::class, 'show'])->name('progress.show');
    Route::get('/progress/{progress}/edit', [\App\Http\Controllers\Estimator\ProgressController::class, 'edit'])->name('progress.edit');
    Route::put('/progress/{progress}', [\App\Http\Controllers\Estimator\ProgressController::class, 'update'])->name('progress.update');
    Route::delete('/progress/{progress}', [\App\Http\Controllers\Estimator\ProgressController::class, 'destroy'])->name('progress.destroy');
});
require __DIR__.'/auth.php';
