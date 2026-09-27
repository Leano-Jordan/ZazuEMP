<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BusinessCapabilityController;
use App\Http\Controllers\BusinessContextController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ComplianceDocumentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerContactController;
use App\Http\Controllers\EventCostController;
use App\Http\Controllers\EventPreparationController;
use App\Http\Controllers\EventAttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TravelCostController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'create'])->name('login');
    Route::get('/owner/login', [\App\Http\Controllers\AuthController::class, 'create'])->defaults('owner', true)->name('owner.login');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->middleware('throttle:password.email')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:password.reset')->name('password.update');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'storeRegistration'])->middleware('throttle:register')->name('register.store');
});

Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/owner', [\App\Http\Controllers\AuthController::class, 'owner'])->name('owner.dashboard');
});

Route::middleware(['auth', 'auth.session', 'business.context'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('owner')->group(function () {
        Route::get('/setup', [OnboardingController::class, 'index'])->name('onboarding.index');
        Route::get('/setup/catalogue', [OnboardingController::class, 'catalogue'])->name('onboarding.catalogue');
        Route::post('/setup/catalogue', [OnboardingController::class, 'storeCatalogue'])->name('onboarding.catalogue.store');
        Route::post('/setup/catalogue/finish', [OnboardingController::class, 'finishCatalogue'])->name('onboarding.catalogue.finish');
        Route::post('/setup/catalogue/skip', [OnboardingController::class, 'skipCatalogue'])->name('onboarding.catalogue.skip');
        Route::get('/setup/business', [OnboardingController::class, 'business'])->name('onboarding.business');
        Route::post('/setup/business', [OnboardingController::class, 'storeBusiness'])->name('onboarding.business.store');
        Route::post('/setup/business/skip', [OnboardingController::class, 'skipBusiness'])->name('onboarding.business.skip');
    });

    Route::get('/media/profile/{type}/{id}', [\App\Http\Controllers\ProfileMediaController::class, 'show'])->whereIn('type', ['customer', 'user'])->name('profile.media');
    Route::get('/media/business/{type}', [\App\Http\Controllers\BusinessMediaController::class, 'show'])->whereIn('type', ['logo', 'dashboard', 'wallpaper'])->name('business.media');
    Route::get('/calendar', CalendarController::class)->name('calendar.index');

    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/finance/invoices/create', [FinanceController::class, 'createInvoice'])->name('finance.invoices.create');
    Route::get('/finance/invoices/{invoice}', [FinanceController::class, 'showInvoice'])->name('finance.invoices.show');
    Route::post('/finance/invoices', [FinanceController::class, 'storeInvoice'])->name('finance.invoices.store');
    Route::get('/finance/payments/create', [FinanceController::class, 'createPayment'])->name('finance.payments.create');
    Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
    Route::get('/finance/expenses/create', [FinanceController::class, 'createExpense'])->name('finance.expenses.create');
    Route::post('/finance/expenses', [FinanceController::class, 'storeExpense'])->name('finance.expenses.store');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('/purchasing', [PurchaseOrderController::class, 'index'])->name('purchasing.index');
    Route::get('/purchasing/create', [PurchaseOrderController::class, 'create'])->name('purchasing.create');
    Route::post('/purchasing', [PurchaseOrderController::class, 'store'])->name('purchasing.store');
    Route::get('/purchasing/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchasing.show');
    Route::patch('/purchasing/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchasing.status');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/{inventoryItem}/movement', [InventoryController::class, 'movement'])->name('inventory.movement');

    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::post('/assets/{asset}/allocate', [AssetController::class, 'allocate'])->name('assets.allocate');
    Route::post('/assets/{asset}/release', [AssetController::class, 'release'])->name('assets.release');

    Route::get('/reports', \App\Http\Controllers\ReportController::class)->name('reports.index');

    Route::middleware('owner')->group(function () {
        Route::get('/settings', [BusinessSettingsController::class, 'edit'])->name('settings.index');
        Route::put('/settings', [BusinessSettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/compliance', [ComplianceDocumentController::class, 'index'])->name('settings.compliance');
        Route::get('/settings/compliance/pack', [ComplianceDocumentController::class, 'pack'])->name('settings.compliance.pack');
        Route::post('/settings/compliance', [ComplianceDocumentController::class, 'store'])->name('settings.compliance.store');
        Route::get('/settings/compliance/{document}/download', [ComplianceDocumentController::class, 'download'])->name('settings.compliance.download');
        Route::delete('/settings/compliance/{document}', [ComplianceDocumentController::class, 'destroy'])->name('settings.compliance.destroy');
    });

    Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::post('/quotes/{quote}/versions', [QuoteController::class, 'createVersion'])->name('quotes.versions.store');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
    Route::get('/quotes/{quote}/versions/{version}/edit', [QuoteController::class, 'editVersion'])->name('quotes.versions.edit');
    Route::put('/quotes/{quote}/versions/{version}', [QuoteController::class, 'updateVersion'])->name('quotes.versions.update');

    Route::get('/work', [WorkController::class, 'index'])->name('work.index');
    Route::get('/work/create', [WorkController::class, 'create'])->name('work.create');
    Route::post('/work', [WorkController::class, 'store'])->name('work.store');
    Route::get('/work/{event}', [WorkController::class, 'show'])->name('work.show');
    Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->name('work.edit');
    Route::put('/work/{event}', [WorkController::class, 'update'])->name('work.update');
    Route::delete('/work/{event}', [WorkController::class, 'destroy'])->name('work.destroy');
    Route::post('/work/{event}/attachments', [EventAttachmentController::class, 'store'])->name('work.attachments.store');
    Route::get('/work/attachments/{attachment}', [EventAttachmentController::class, 'download'])->name('work.attachments.download');
    Route::delete('/work/attachments/{attachment}', [EventAttachmentController::class, 'destroy'])->name('work.attachments.destroy');

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
});

Route::middleware('auth')->group(function () {
    Route::post('/business/switch', [BusinessContextController::class, 'change'])->name('business.switch');
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'destroy'])->name('logout');
});
