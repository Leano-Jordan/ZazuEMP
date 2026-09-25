<?php

use App\Http\Controllers\BusinessCapabilityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('work.index'));

Route::get('/work', [WorkController::class, 'index'])->name('work.index');
Route::get('/work/create', [WorkController::class, 'create'])->name('work.create');
Route::post('/work', [WorkController::class, 'store'])->name('work.store');
Route::get('/work/{event}', [WorkController::class, 'show'])->name('work.show');
Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->name('work.edit');
Route::put('/work/{event}', [WorkController::class, 'update'])->name('work.update');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

Route::get('/capabilities', [BusinessCapabilityController::class, 'index'])->name('capabilities.index');
Route::get('/capabilities/create', [BusinessCapabilityController::class, 'create'])->name('capabilities.create');
Route::post('/capabilities', [BusinessCapabilityController::class, 'store'])->name('capabilities.store');
Route::get('/capabilities/{capability}/edit', [BusinessCapabilityController::class, 'edit'])->name('capabilities.edit');
Route::put('/capabilities/{capability}', [BusinessCapabilityController::class, 'update'])->name('capabilities.update');
