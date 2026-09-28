<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditLogController;
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
use App\Http\Controllers\ExperiencePreferenceController;
use App\Http\Controllers\SearchController;
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

Route::get('/', function (\Illuminate\Http\Request $request) {
    $isAuthenticated = (bool) $request->user();
    $business = $isAuthenticated
        ? app(\App\Support\CurrentBusiness::class)->resolve($request->user())
        : null;
    $hasActiveWorkspace = $business !== null;

    $telemetry = [
        'all_work' => 1,
        'today' => 0,
        'next_7_days' => 1,
        'in_progress' => 0,
        'drafts' => 1,
        'record_name' => 'Workspace preview',
        'record_meta' => 'Live operational preview',
        'status' => 'PREVIEW',
        'date' => '30 Sep 2026',
        'event_type' => 'Event operations',
        'reference' => 'ZAZU-PREVIEW',
        'reference_meta' => 'Sign in for live workspace data',
    ];

    if ($hasActiveWorkspace) {
        $today = now()->startOfDay();
        $active = \App\Models\Event::query()
            ->where('business_id', $business->id)
            ->whereNotIn('status', \App\Models\Event::TERMINAL_STATUSES);

        $latest = (clone $active)->latest('event_date')->latest()->first();

        $telemetry = [
            'all_work' => (clone $active)->count(),
            'today' => (clone $active)->whereDate('event_date', $today)->count(),
            'next_7_days' => (clone $active)->whereBetween('event_date', [$today, $today->copy()->addDays(6)])->count(),
            'in_progress' => (clone $active)->where('status', 'in_progress')->count(),
            'drafts' => (clone $active)->where('status', 'draft')->count(),
            'record_name' => $latest?->customer_name ?: $business->name,
            'record_meta' => $latest?->name ?: 'No active work selected',
            'status' => strtoupper($latest?->status ?: 'READY'),
            'date' => $latest?->event_date?->format('d M Y') ?: '—',
            'event_type' => $latest?->event_type ?: 'Workspace',
            'reference' => $latest?->reference ?: 'NO ACTIVE JOB',
            'reference_meta' => $latest ? 'Current workspace record' : 'No active work',
        ];
    }

    return view('landing', compact('telemetry', 'isAuthenticated', 'hasActiveWorkspace', 'business'));
})->name('landing');

Route::middleware('signed')->group(function () {
    Route::get('/quotes/{quote}/view', [QuoteController::class, 'publicShow'])->name('quotes.public');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'publicAccept'])->name('quotes.public.accept');
});

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

Route::middleware(['auth', 'auth.session', 'owner'])->group(function () {
    Route::get('/owner', [\App\Http\Controllers\AuthController::class, 'owner'])->name('owner.dashboard');
});

Route::middleware(['auth', 'auth.session', 'business.context'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::middleware('owner')->group(function () {
        Route::get('/setup', [OnboardingController::class, 'index'])->name('onboarding.index');
        Route::get('/setup/catalogue', [OnboardingController::class, 'catalogue'])->name('onboarding.catalogue');
        Route::post('/setup/catalogue', [OnboardingController::class, 'storeCatalogue'])->name('onboarding.catalogue.store');
        Route::post('/setup/catalogue/finish', [OnboardingController::class, 'finishCatalogue'])->name('onboarding.catalogue.finish');
        Route::post('/setup/catalogue/skip', [OnboardingController::class, 'skipCatalogue'])->name('onboarding.catalogue.skip');
        Route::get('/setup/experience', [OnboardingController::class, 'experience'])->name('onboarding.experience');
        Route::post('/setup/experience', [OnboardingController::class, 'storeExperience'])->name('onboarding.experience.store');
        Route::get('/setup/business', [OnboardingController::class, 'business'])->name('onboarding.business');
        Route::post('/setup/business', [OnboardingController::class, 'storeBusiness'])->name('onboarding.business.store');
        Route::post('/setup/business/skip', [OnboardingController::class, 'skipBusiness'])->name('onboarding.business.skip');
    });

    Route::get('/media/profile/{type}/{id}', [\App\Http\Controllers\ProfileMediaController::class, 'show'])->whereIn('type', ['customer', 'user'])->name('profile.media');
    Route::get('/media/business/{type}', [\App\Http\Controllers\BusinessMediaController::class, 'show'])->whereIn('type', ['logo', 'dashboard', 'wallpaper'])->name('business.media');
    Route::get('/calendar', CalendarController::class)->middleware('permission:calendar.view')->name('calendar.index');
    Route::get('/search', SearchController::class)->name('search.index');
    Route::get('/preferences/experience', [ExperiencePreferenceController::class, 'edit'])->name('preferences.experience');
    Route::put('/preferences/experience', [ExperiencePreferenceController::class, 'update'])->name('preferences.experience.update');

    Route::get('/finance', [FinanceController::class, 'index'])->middleware('permission:finance.view')->name('finance.index');
    Route::get('/finance/invoices/create', [FinanceController::class, 'createInvoice'])->middleware('permission:finance.invoice.create')->name('finance.invoices.create');
    Route::get('/finance/invoices/{invoice}', [FinanceController::class, 'showInvoice'])->middleware('permission:finance.view')->name('finance.invoices.show');
    Route::post('/finance/invoices', [FinanceController::class, 'storeInvoice'])->middleware('permission:finance.invoice.create')->name('finance.invoices.store');
    Route::get('/finance/payments/create', [FinanceController::class, 'createPayment'])->middleware('permission:finance.payment.create')->name('finance.payments.create');
    Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->middleware('permission:finance.payment.create')->name('finance.payments.store');
    Route::get('/finance/expenses/create', [FinanceController::class, 'createExpense'])->middleware('permission:finance.expense.create')->name('finance.expenses.create');
    Route::post('/finance/expenses', [FinanceController::class, 'storeExpense'])->middleware('permission:finance.expense.create')->name('finance.expenses.store');

    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:suppliers.view')->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->middleware('permission:suppliers.create')->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:suppliers.create')->name('suppliers.store');

    Route::get('/purchasing', [PurchaseOrderController::class, 'index'])->middleware('permission:purchasing.view')->name('purchasing.index');
    Route::get('/purchasing/create', [PurchaseOrderController::class, 'create'])->middleware('permission:purchasing.create')->name('purchasing.create');
    Route::post('/purchasing', [PurchaseOrderController::class, 'store'])->middleware('permission:purchasing.create')->name('purchasing.store');
    Route::get('/purchasing/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('permission:purchasing.view')->name('purchasing.show');
    Route::patch('/purchasing/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->middleware('permission:purchasing.status')->name('purchasing.status');
    Route::post('/purchasing/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->middleware('permission:purchasing.status')->name('purchasing.receive');

    Route::get('/inventory', [InventoryController::class, 'index'])->middleware('permission:inventory.view')->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->middleware('permission:inventory.create')->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->middleware('permission:inventory.create')->name('inventory.store');
    Route::post('/inventory/{inventoryItem}/movement', [InventoryController::class, 'movement'])->middleware('permission:inventory.movement')->name('inventory.movement');

    Route::get('/assets', [AssetController::class, 'index'])->middleware('permission:assets.view')->name('assets.index');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->middleware('permission:assets.update')->name('assets.edit');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->middleware('permission:assets.update')->name('assets.update');

    Route::get('/assets/create', [AssetController::class, 'create'])->middleware('permission:assets.create')->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->middleware('permission:assets.create')->name('assets.store');
    Route::post('/assets/{asset}/allocate', [AssetController::class, 'allocate'])->middleware('permission:assets.allocate')->name('assets.allocate');
    Route::post('/assets/{asset}/release', [AssetController::class, 'release'])->middleware('permission:assets.release')->name('assets.release');

    Route::get('/reports', \App\Http\Controllers\ReportController::class)->middleware('permission:reports.view')->name('reports.index');

    Route::middleware('owner')->group(function () {
        Route::get('/settings', [BusinessSettingsController::class, 'edit'])->name('settings.index');
        Route::get('/settings/audit', [AuditLogController::class, 'index'])->name('settings.audit');
        Route::put('/settings', [BusinessSettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/compliance', [ComplianceDocumentController::class, 'index'])->name('settings.compliance');
        Route::get('/settings/compliance/pack', [ComplianceDocumentController::class, 'pack'])->name('settings.compliance.pack');
        Route::post('/settings/compliance', [ComplianceDocumentController::class, 'store'])->name('settings.compliance.store');
        Route::get('/settings/compliance/{document}/download', [ComplianceDocumentController::class, 'download'])->name('settings.compliance.download');
        Route::delete('/settings/compliance/{document}', [ComplianceDocumentController::class, 'destroy'])->name('settings.compliance.destroy');
    });

    Route::get('/quotes', [QuoteController::class, 'index'])->middleware('permission:quotes.view')->name('quotes.index');
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->middleware('permission:quotes.view')->name('quotes.show');
    Route::post('/quotes/{quote}/versions', [QuoteController::class, 'createVersion'])->middleware('permission:quotes.create')->name('quotes.versions.store');
    Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->middleware('permission:quotes.status')->name('quotes.status');
    Route::get('/quotes/{quote}/versions/{version}/edit', [QuoteController::class, 'editVersion'])->middleware('permission:quotes.update')->name('quotes.versions.edit');
    Route::put('/quotes/{quote}/versions/{version}', [QuoteController::class, 'updateVersion'])->middleware('permission:quotes.update')->name('quotes.versions.update');

    Route::get('/work', [WorkController::class, 'index'])->middleware('permission:work.view')->name('work.index');
    Route::get('/work/create', [WorkController::class, 'create'])->middleware('permission:work.create')->name('work.create');
    Route::post('/work', [WorkController::class, 'store'])->middleware('permission:work.create')->name('work.store');
    Route::get('/work/{event}', [WorkController::class, 'show'])->middleware('permission:work.view')->name('work.show');
    Route::get('/work/{event}/edit', [WorkController::class, 'edit'])->middleware('permission:work.update')->name('work.edit');
    Route::put('/work/{event}', [WorkController::class, 'update'])->middleware('permission:work.update')->name('work.update');
    Route::delete('/work/{event}', [WorkController::class, 'destroy'])->middleware('permission:work.delete')->name('work.destroy');
    Route::post('/work/{event}/attachments', [EventAttachmentController::class, 'store'])->middleware('permission:work.update')->name('work.attachments.store');
    Route::get('/work/attachments/{attachment}', [EventAttachmentController::class, 'download'])->middleware('permission:work.view')->name('work.attachments.download');
    Route::delete('/work/attachments/{attachment}', [EventAttachmentController::class, 'destroy'])->middleware('permission:work.update')->name('work.attachments.destroy');

    Route::get('/work/{event}/travel', [TravelCostController::class, 'index'])->middleware('permission:work.view')->name('work.travel.index');
    Route::get('/work/{event}/travel/create', [TravelCostController::class, 'create'])->middleware('permission:work.update')->name('work.travel.create');
    Route::post('/work/{event}/travel', [TravelCostController::class, 'store'])->middleware('permission:work.update')->name('work.travel.store');
    Route::get('/work/{event}/costs', [EventCostController::class, 'index'])->middleware('permission:work.view')->name('work.costs.index');
    Route::get('/work/{event}/costs/create', [EventCostController::class, 'create'])->middleware('permission:work.update')->name('work.costs.create');
    Route::post('/work/{event}/costs', [EventCostController::class, 'store'])->middleware('permission:work.update')->name('work.costs.store');
    Route::get('/work/{event}/preparation', [EventPreparationController::class, 'index'])->middleware('permission:work.view')->name('work.preparation.index');
    Route::get('/work/{event}/preparation/create', [EventPreparationController::class, 'create'])->middleware('permission:work.update')->name('work.preparation.create');
    Route::post('/work/{event}/preparation', [EventPreparationController::class, 'store'])->middleware('permission:work.update')->name('work.preparation.store');
    Route::patch('/work/{event}/preparation/{item}/status', [EventPreparationController::class, 'updateStatus'])->middleware('permission:work.update')->name('work.preparation.status');
    Route::get('/work/{event}/quotes', [QuoteController::class, 'eventIndex'])
        ->middleware('permission:quotes.view')
        ->name('work.quotes.index');
    Route::get('/work/{event}/quotes/create', [QuoteController::class, 'create'])
        ->middleware('permission:quotes.create')
        ->name('work.quotes.create');
    Route::post('/work/{event}/quotes', [QuoteController::class, 'store'])
        ->middleware('permission:quotes.create')
        ->name('work.quotes.store');
    Route::get('/work/{event}/requirements', [RequirementController::class, 'index'])
        ->middleware('permission:work.view')
        ->name('work.requirements.index');
    Route::get('/work/{event}/requirements/create', [RequirementController::class, 'create'])
        ->middleware('permission:work.update')
        ->name('work.requirements.create');
    Route::post('/work/{event}/requirements', [RequirementController::class, 'store'])
        ->middleware('permission:work.update')
        ->name('work.requirements.store');

    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->middleware('permission:customers.create')->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:customers.create')->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view')->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('permission:customers.update')->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:customers.update')->name('customers.update');
    Route::get('/customers/{customer}/contacts/create', [CustomerContactController::class, 'create'])->middleware('permission:customers.contacts.manage')->name('customers.contacts.create');
    Route::post('/customers/{customer}/contacts', [CustomerContactController::class, 'store'])->middleware('permission:customers.contacts.manage')->name('customers.contacts.store');
    Route::get('/customers/{customer}/contacts/{contact}/edit', [CustomerContactController::class, 'edit'])->middleware('permission:customers.contacts.manage')->name('customers.contacts.edit');
    Route::put('/customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'update'])->middleware('permission:customers.contacts.manage')->name('customers.contacts.update');
    Route::delete('/customers/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy'])->middleware('permission:customers.contacts.manage')->name('customers.contacts.destroy');

    Route::get('/capabilities', [BusinessCapabilityController::class, 'index'])->middleware('permission:capabilities.view')->name('capabilities.index');
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
