<?php

use App\Http\Controllers\BusinessCapabilityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));


Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::view('/quotes', 'quotes.index')->name('quotes.index');
Route::view('/calendar', 'calendar.index')->name('calendar.index');
Route::view('/suppliers', 'suppliers.index')->name('suppliers.index');
Route::view('/inventory', 'inventory.index')->name('inventory.index');
Route::view('/assets', 'assets.index')->name('assets.index');
Route::view('/reports', 'reports.index')->name('reports.index');
Route::view('/settings', 'settings.index')->name('settings.index');

Route::get('/work', [WorkController::class, 'index'])->name('work.index');
Route::get('/work/create', [WorkController::class, 'create'])->name('work.create');
Route::post('/work', [WorkController::class, 'store'])->name('work.store');
Route::get('/work/{event}', [WorkController::class, 'show'])->name('work.show');
Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->name('work.edit');
Route::put('/work/{event}', [WorkController::class, 'update'])->name('work.update');

Route::get('/work/{event}/requirements', [RequirementController::class, 'index'])->name('work.requirements.index');
Route::get('/work/{event}/requirements/create', [RequirementController::class, 'create'])->name('work.requirements.create');
Route::post('/work/{event}/requirements', [RequirementController::class, 'store'])->name('work.requirements.store');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

Route::get('/capabilities', [BusinessCapabilityController::class, 'index'])->name('capabilities.index');
Route::get('/capabilities/create', [BusinessCapabilityController::class, 'create'])->name('capabilities.create');
Route::post('/capabilities', [BusinessCapabilityController::class, 'store'])->name('capabilities.store');
Route::get('/capabilities/{capability}/edit', [BusinessCapabilityController::class, 'edit'])->name('capabilities.edit');
Route::put('/capabilities/{capability}', [BusinessCapabilityController::class, 'update'])->name('capabilities.update');
