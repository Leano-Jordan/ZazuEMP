<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event as WorkEvent;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteVersion;
use App\Models\Supplier;
use App\Support\Offline\SyncEntityRevisionTracker;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerLocalPhpIniPassthrough();
        $this->registerRevisionTracking();
        $this->registerRateLimiters();
    }

    /**
     * Pass the local PHP scan-directory override into Laravel's child dev-server process.
     *
     * Laravel filters inherited environment variables on Windows; without this allowlist
     * entry, PHP_INI_SCAN_DIR set by the Zazu launcher reaches Artisan but not the web server.
     */
    private function registerLocalPhpIniPassthrough(): void
    {
        if (! in_array('PHP_INI_SCAN_DIR', ServeCommand::$passthroughVariables, true)) {
            ServeCommand::$passthroughVariables[] = 'PHP_INI_SCAN_DIR';
        }
    }

    private function registerRevisionTracking(): void
    {
        $revisionTracker = app(SyncEntityRevisionTracker::class);

        $this->registerPrimaryRevisionListeners($revisionTracker);
        $this->registerNestedRevisionListeners($revisionTracker);

        Event::listen('eloquent.created: '.AssetAllocation::class, function ($record) use ($revisionTracker): void {
            $revisionTracker->touch($record, 'asset_allocation');
        });
        Event::listen('eloquent.updated: '.AssetAllocation::class, function ($record) use ($revisionTracker): void {
            $revisionTracker->touch($record, 'asset_allocation');
        });
        Event::listen('eloquent.deleting: '.AssetAllocation::class, function ($record) use ($revisionTracker): void {
            $revisionTracker->touch($record, 'asset_allocation');
        });
        Event::listen('eloquent.restored: '.AssetAllocation::class, function ($record) use ($revisionTracker): void {
            $revisionTracker->touch($record, 'asset_allocation');
        });
    }

    private function registerPrimaryRevisionListeners(SyncEntityRevisionTracker $revisionTracker): void
    {
        foreach ([
            Customer::class => 'customer',
            WorkEvent::class => 'job',
            Quote::class => 'quote',
            BusinessCapability::class => 'service',
            EventPreparationItem::class => 'preparation',
            Supplier::class => 'supplier',
            PurchaseOrder::class => 'purchase_order',
            Invoice::class => 'invoice',
            FinanceExpense::class => 'expense',
            InventoryItem::class => 'inventory_item',
            Asset::class => 'asset',
            EventCost::class => 'event_cost',
        ] as $model => $entityType) {
            foreach (['updated', 'deleting', 'restored'] as $event) {
                Event::listen('eloquent.'.$event.': '.$model, function ($record) use ($revisionTracker, $entityType): void {
                    $revisionTracker->touch($record, $entityType);
                });
            }
        }
    }

    private function registerNestedRevisionListeners(SyncEntityRevisionTracker $revisionTracker): void
    {
        $nestedRevisionParents = [
            CustomerContact::class => ['customer', 'customer'],
            EventRequirement::class => ['event', 'job'],
            QuoteVersion::class => ['quote', 'quote'],
            QuoteItem::class => ['version.quote', 'quote'],
            InvoiceItem::class => ['invoice', 'invoice'],
            Payment::class => ['invoice', 'invoice'],
            PurchaseOrderItem::class => ['purchaseOrder', 'purchase_order'],
            PurchaseOrderReceipt::class => ['purchaseOrder', 'purchase_order'],
            InventoryMovement::class => ['item', 'inventory_item'],
            AssetAllocation::class => ['asset', 'asset'],
        ];

        foreach ($nestedRevisionParents as $model => [$relation, $entityType]) {
            foreach (['created', 'updated', 'deleting', 'restored'] as $event) {
                Event::listen('eloquent.'.$event.': '.$model, function ($record) use ($revisionTracker, $relation, $entityType): void {
                    $revisionTracker->touchRelated($record, $relation, $entityType);
                });
            }
        }
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $identifier = Str::lower(trim($request->string('identifier')->toString()));

            return [
                Limit::perMinute(6)->by('login:identifier-ip:'.$identifier.'|'.$request->ip()),
                Limit::perMinute(30)->by('login:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password.email', function (Request $request) {
            $identifier = Str::lower(trim($request->string('identifier')->toString()));

            return [
                Limit::perMinute(5)->by('password-email:'.$identifier.'|'.$request->ip()),
                Limit::perMinute(20)->by('password-email:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password.reset', function (Request $request) {
            return Limit::perMinute(10)->by('password-reset:ip:'.$request->ip());
        });

        RateLimiter::for('quote.public', function (Request $request) {
            return Limit::perMinute(60)->by('quote-public:'.$request->ip());
        });

        RateLimiter::for('business-archive-export', function (Request $request) {
            $identity = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinutes(10, 3)->by('business-archive-export:'.$identity);
        });

        RateLimiter::for('business-archive-restore', function (Request $request) {
            $identity = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinutes(10, 3)->by('business-archive-restore:'.$identity);
        });

        RateLimiter::for('sync', function (Request $request) {
            $token = $request->bearerToken();

            return Limit::perMinute(120)->by('sync:'.($token ? hash('sha256', $token) : $request->ip()));
        });
    }

}
