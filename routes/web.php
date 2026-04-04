<?php

use App\Http\Controllers\DeptController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // 所属マスタ
    Route::get('depts/export', [DeptController::class, 'export'])->name('depts.export');
    Route::resource('depts', DeptController::class)->except(['show']);

    // 社員マスタ
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class)->except(['show']);
});

require __DIR__.'/settings.php';
