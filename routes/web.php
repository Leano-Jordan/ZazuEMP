<?php

use App\Http\Controllers\BusinessCapabilityController;
use App\Http\Controllers\BusinessContextController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerContactController;
use App\Http\Controllers\EventCostController;
use App\Http\Controllers\EventPreparationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\ResourceOverviewController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\TravelCostController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
    Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'storeRegistration'])
        ->middleware('throttle:register')
        ->name('register.store');
});

Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/owner', [\App\Http\Controllers\AuthController::class, 'owner'])->name('owner.dashboard');
});


Route::middleware('auth')->group(function () {



Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::get('/calendar', CalendarController::class)->name('calendar.index');
Route::get('/suppliers', [ResourceOverviewController::class, 'suppliers'])->name('suppliers.index');
Route::get('/inventory', [ResourceOverviewController::class, 'inventory'])->name('inventory.index');
Route::get('/assets', [ResourceOverviewController::class, 'assets'])->name('assets.index');
Route::get('/reports', \App\Http\Controllers\ReportController::class)->name('reports.index');
Route::middleware('owner')->group(function () {
    Route::get('/settings', [BusinessSettingsController::class, 'edit'])->name('settings.index');
    Route::put('/settings', [BusinessSettingsController::class, 'update'])->name('settings.update');
});
Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::post('/quotes/{quote}/versions', [QuoteController::class, 'createVersion'])->name('quotes.versions.store');

Route::get('/work', [WorkController::class, 'index'])->name('work.index');
Route::get('/work/create', [WorkController::class, 'create'])->name('work.create');
Route::post('/work', [WorkController::class, 'store'])->name('work.store');
Route::get('/work/{event}', [WorkController::class, 'show'])->name('work.show');
Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->name('work.edit');
Route::put('/work/{event}', [WorkController::class, 'update'])->name('work.update');
Route::delete('/work/{event}', [WorkController::class, 'destroy'])->name('work.destroy');

Route::get('/work/{event}/travel', [TravelCostController::class, 'index'])->name('work.travel.index');
Route::get('/work/{event}/travel/create', [TravelCostController::class, 'create'])->name('work.travel.create');
Route::post('/work/{event}/travel', [TravelCostController::class, 'store'])->name('work.travel.store');

Route::get('/work/{event}/costs', [EventCostController::class, 'index'])->name('work.costs.index');
Route::get('/work/{event}/costs/create', [EventCostController::class, 'create'])->name('work.costs.create');
Route::post('/work/{event}/costs', [EventCostController::class, 'store'])->name('work.costs.store');

Route::get('/work/{event}/preparation', [EventPreparationController::class, 'index'])->name('work.preparation.index');
Route::get('/work/{event}/preparation/create', [EventPreparationController::class, 'create'])->name('work.preparation.create');
Route::post('/work/{event}/preparation', [EventPreparationController::class, 'store'])->name('work.preparation.store');
Route::patch('/work/{event}/preparation/{item}/status', [EventPreparationController::class, 'updateStatus'])->name('work.preparation.status');

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
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::get('/customers/{customer}/contacts/create', [CustomerContactController::class, 'create'])->name('customers.contacts.create');
Route::post('/customers/{customer}/contacts', [CustomerContactController::class, 'store'])->name('customers.contacts.store');
Route::get('/customers/{customer}/contacts/{contact}/edit', [CustomerContactController::class, 'edit'])->name('customers.contacts.edit');
Route::put('/customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'update'])->name('customers.contacts.update');
Route::delete('/customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy'])->name('customers.contacts.destroy');

Route::get('/capabilities', [BusinessCapabilityController::class, 'index'])->name('capabilities.index');
Route::middleware('owner')->group(function () {
    Route::get('/capabilities/create', [BusinessCapabilityController::class, 'create'])->name('capabilities.create');
    Route::post('/capabilities', [BusinessCapabilityController::class, 'store'])->name('capabilities.store');
    Route::get('/capabilities/{capability}/edit', [BusinessCapabilityController::class, 'edit'])->name('capabilities.edit');
    Route::put('/capabilities/{capability}', [BusinessCapabilityController::class, 'update'])->name('capabilities.update');
});


    Route::post('/business/switch', [BusinessContextController::class, 'change'])->name('business.switch');

    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'destroy'])->name('logout');
});
