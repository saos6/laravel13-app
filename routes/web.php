<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeptController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // 所属マスタ
    Route::get('depts/export', [DeptController::class, 'export'])->name('depts.export');
    Route::resource('depts', DeptController::class)->except(['show']);

    // 社員マスタ
    Route::get('employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeController::class)->except(['show']);

    // 得意先マスタ
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('customers/{customer}/replicate', [CustomerController::class, 'replicate'])->name('customers.replicate');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // 商品マスタ
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/{product}/replicate', [ProductController::class, 'replicate'])->name('products.replicate');
    Route::resource('products', ProductController::class)->except(['show']);

    // 見積
    Route::get('quotes/export', [QuoteController::class, 'exportMethod'])->name('quotes.export');
    Route::get('quotes/{quote}/replicate', [QuoteController::class, 'replicate'])->name('quotes.replicate');
    Route::resource('quotes', QuoteController::class);
});

require __DIR__.'/settings.php';
