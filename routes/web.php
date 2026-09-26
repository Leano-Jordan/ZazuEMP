<?php

use App\Http\Controllers\BusinessCapabilityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\TravelCostController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));


Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::view('/calendar', 'calendar.index')->name('calendar.index');
Route::view('/suppliers', 'suppliers.index')->name('suppliers.index');
Route::view('/inventory', 'inventory.index')->name('inventory.index');
Route::view('/assets', 'assets.index')->name('assets.index');
Route::view('/reports', 'reports.index')->name('reports.index');
Route::view('/settings', 'settings.index')->name('settings.index');
Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::post('/quotes/{quote}/versions', [QuoteController::class, 'createVersion'])->name('quotes.versions.store');

Route::get('/work', [WorkController::class, 'index'])->name('work.index');
Route::get('/work/create', [WorkController::class, 'create'])->name('work.create');
Route::post('/work', [WorkController::class, 'store'])->name('work.store');
Route::get('/work/{event}', [WorkController::class, 'show'])->name('work.show');
Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->name('work.edit');
Route::put('/work/{event}', [WorkController::class, 'update'])->name('work.update');

Route::get('/work/{event}/travel', [TravelCostController::class, 'index'])->name('work.travel.index');
Route::get('/work/{event}/travel/create', [TravelCostController::class, 'create'])->name('work.travel.create');
Route::post('/work/{event}/travel', [TravelCostController::class, 'store'])->name('work.travel.store');

Route::get('/work/{event}/quotes', [QuoteController::class, 'eventIndex'])->name('work.quotes.index');
Route::get('/work/{event}/quotes/create', [QuoteController::class, 'create'])->name('work.quotes.create');
Route::post('/work/{event}/quotes', [QuoteController::class, 'store'])->name('work.quotes.store');

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
