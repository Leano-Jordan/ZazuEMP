<?php

namespace App\Support;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\PurchaseOrder;
use App\Models\User;

class ZazuHelperService
{
    private const ATTENTION_ROUTES = [
        'dashboard',
        'work.index',
        'purchasing.index',
        'finance.index',
        'onboarding.index',
    ];

    public function attention(User $user, Business $business, string $routeName): array
    {
        if (!in_array($routeName, self::ATTENTION_ROUTES, true)) {
            return [];
        }

        $items = [];

        $this->addOwnerSetupAttention($items, $user, $business);
        $this->addOverduePreparationAttention($items, $user, $business);
        $this->addPurchasingAttention($items, $user, $business);
        $this->addDraftWorkAttention($items, $user, $business);

        return array_slice($items, 0, 4);
    }

    private function addOwnerSetupAttention(array &$items, User $user, Business $business): void
    {
        if (!$user->businesses()->whereKey($business->id)->wherePivot('role', 'owner')->exists()) {
            return;
        }

        if ($business->catalogue_setup_completed_at && $business->business_setup_completed_at) {
            return;
        }

        $items[] = [
            'title' => 'Workspace setup is still open',
            'copy' => 'Finish or deliberately defer the remaining setup steps so the workspace reflects your operating context.',
            'href' => route('onboarding.index'),
            'link' => 'Review setup',
        ];
    }

    private function addOverduePreparationAttention(array &$items, User $user, Business $business): void
    {
        if (!app(PermissionService::class)->allows('work.view', $user, $business)) {
            return;
        }

        $overdue = EventPreparationItem::query()
            ->where('business_id', $business->id)
            ->whereIn('status', ['open', 'blocked'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->startOfDay())
            ->whereHas('event', fn ($query) => $query
                ->where('business_id', $business->id)
                ->whereNotIn('status', Event::TERMINAL_STATUSES))
            ->count();

        if ($overdue === 0) {
            return;
        }

        $items[] = [
            'title' => $overdue.' preparation item'.($overdue === 1 ? '' : 's').' overdue',
            'copy' => 'Review the affected jobs and clear or re-plan the outstanding operational work.',
            'href' => route('work.index', ['filter' => 'overdue']),
            'link' => 'Open overdue work',
        ];
    }

    private function addPurchasingAttention(array &$items, User $user, Business $business): void
    {
        if (!app(PermissionService::class)->allows('purchasing.view', $user, $business)) {
            return;
        }

        $openPurchases = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->whereIn('status', ['draft', 'sent', 'ordered'])
            ->count();

        if ($openPurchases === 0) {
            return;
        }

        $items[] = [
            'title' => $openPurchases.' purchase order'.($openPurchases === 1 ? '' : 's').' still open',
            'copy' => 'Check outstanding purchasing commitments before they become execution-day surprises.',
            'href' => route('purchasing.index'),
            'link' => 'Review purchasing',
        ];
    }

    private function addDraftWorkAttention(array &$items, User $user, Business $business): void
    {
        if (!app(PermissionService::class)->allows('work.view', $user, $business)) {
            return;
        }

        $drafts = Event::query()
            ->where('business_id', $business->id)
            ->where('status', 'draft')
            ->count();

        if ($drafts === 0) {
            return;
        }

        $items[] = [
            'title' => $drafts.' job'.($drafts === 1 ? '' : 's').' still in draft',
            'copy' => 'Confirm the operational state when the job is ready to move beyond planning.',
            'href' => route('work.index', ['filter' => 'draft']),
            'link' => 'Open draft work',
        ];
    }
}
