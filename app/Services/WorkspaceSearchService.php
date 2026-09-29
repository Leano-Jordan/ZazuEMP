<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use App\Services\Search\AssetSearchHandler;
use App\Services\Search\CostSearchHandler;
use App\Services\Search\CustomerSearchHandler;
use App\Services\Search\EventSearchHandler;
use App\Services\Search\ExpenseSearchHandler;
use App\Services\Search\InventorySearchHandler;
use App\Services\Search\InvoiceSearchHandler;
use App\Services\Search\PurchaseOrderSearchHandler;
use App\Services\Search\QuoteSearchHandler;
use App\Services\Search\ServiceSearchHandler;
use App\Services\Search\SupplierSearchHandler;

class WorkspaceSearchService
{
    public const TYPES = [
        'customer',
        'event',
        'service',
        'supplier',
        'purchase_order',
        'quote',
        'invoice',
        'cost',
        'asset',
        'inventory',
        'expense',
    ];

    private const HANDLERS = [
        'customer' => CustomerSearchHandler::class,
        'event' => EventSearchHandler::class,
        'service' => ServiceSearchHandler::class,
        'supplier' => SupplierSearchHandler::class,
        'purchase_order' => PurchaseOrderSearchHandler::class,
        'quote' => QuoteSearchHandler::class,
        'invoice' => InvoiceSearchHandler::class,
        'cost' => CostSearchHandler::class,
        'asset' => AssetSearchHandler::class,
        'inventory' => InventorySearchHandler::class,
        'expense' => ExpenseSearchHandler::class,
    ];

    public function search(Business $business, User $user, array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? ''));
        $type = (string) ($filters['type'] ?? '');
        $status = trim((string) ($filters['status'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $types = $type && in_array($type, self::TYPES, true) ? [$type] : self::TYPES;

        $permissions = $this->permissions($user, $business);
        $results = [];

        foreach ($types as $candidate) {
            if (!$this->allowed($candidate, $permissions)) {
                continue;
            }

            $handlerClass = self::HANDLERS[$candidate] ?? null;

            if (!$handlerClass) {
                continue;
            }

            $results = [
                ...$results,
                ...app($handlerClass)->search($business, $query, $status, $from, $to),
            ];
        }

        usort($results, static fn (array $left, array $right): int => strcmp(
            ($right['sort_date'] ?? ''),
            ($left['sort_date'] ?? '')
        ));

        foreach ($results as &$result) {
            unset($result['sort_date']);
        }

        return array_values($results);
    }

    private function permissions(User $user, Business $business): array
    {
        $membership = $user->businesses()
            ->whereKey($business->id)
            ->first();

        $role = $membership?->pivot?->role;

        if ($role === 'owner') {
            return ['*'];
        }

        return config('zazu.permissions.roles.'.$role, []);
    }

    private function allowed(string $type, array $permissions): bool
    {
        return in_array('*', $permissions, true)
            || match ($type) {
                'customer' => in_array('customers.view', $permissions, true),
                'event' => in_array('work.view', $permissions, true),
                'service' => in_array('capabilities.view', $permissions, true),
                'supplier' => in_array('suppliers.view', $permissions, true),
                'purchase_order' => in_array('purchasing.view', $permissions, true),
                'quote' => in_array('quotes.view', $permissions, true),
                'invoice' => in_array('finance.view', $permissions, true),
                'cost' => in_array('work.view', $permissions, true) || in_array('finance.view', $permissions, true),
                'asset' => in_array('assets.view', $permissions, true),
                'inventory' => in_array('inventory.view', $permissions, true),
                'expense' => in_array('finance.view', $permissions, true),
                default => false,
            };
    }
}
