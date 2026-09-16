<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::group(['middleware' => ['auth', 'verified']], function(){
    
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class,'index'])->name('dashboard');

    Route::get('users',[\App\Http\Controllers\UserController::class,'index'])->name('users');

    Route::get('/users/create',[\App\Http\Controllers\UserController::class,'create'])->name('users.create');
    Route::get('/users/edit/{id}',[\App\Http\Controllers\UserController::class,'edit'])->name('users.edit');
    Route::post('/users/store',[\App\Http\Controllers\UserController::class,'store'])->name('users.store');
    Route::patch('/users/{id}/status',[\App\Http\Controllers\UserController::class,'toggleStatus'])->name('users.status');
    Route::patch('/users/{id}/archive',[\App\Http\Controllers\UserController::class,'archive'])->name('users.archive');
    Route::get('/archive', [\App\Http\Controllers\ArchiveController::class, 'index'])->name('archive');
    Route::patch('/archive/users/{id}/restore', [\App\Http\Controllers\ArchiveController::class, 'restoreUser'])->name('archive.users.restore');
    Route::patch('/archive/sales/{id}/restore', [\App\Http\Controllers\ArchiveController::class, 'restoreSale'])->name('archive.sales.restore');

    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');

    Route::get('/sample-crud',action: [\App\Http\Controllers\SampleCrudController::class,'index'])->name('sample-crud');
    Route::get('/sample-crud/create',[\App\Http\Controllers\SampleCrudController::class,'create'])->name('sample-crud.create');
    Route::get('/sample-crud/edit/{id}',[\App\Http\Controllers\SampleCrudController::class,'edit'])->name('sample-crud.edit');
    Route::post('/sample-crud/store',[\App\Http\Controllers\SampleCrudController::class,'store'])->name('sample-crud.store');

    
    // Sales monitoring routes
    Route::get('/sales',[\App\Http\Controllers\SaleController::class,'index'])->name('sales');
    Route::get('/sales/create',[\App\Http\Controllers\SaleController::class,'create'])->name('sales.create');
    Route::get('/sales/edit/{id}',[\App\Http\Controllers\SaleController::class,'edit'])->name('sales.edit');
    Route::post('/sales/store',[\App\Http\Controllers\SaleController::class,'store'])->name('sales.store');
    Route::post('/sales/update/{id}',[\App\Http\Controllers\SaleController::class,'update'])->name('sales.update');
    Route::post('/sales/archive/{id}',[\App\Http\Controllers\SaleController::class,'archive'])->name('sales.archive');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
