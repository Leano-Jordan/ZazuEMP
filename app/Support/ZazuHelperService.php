<?php

namespace App\Support;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\PurchaseOrder;
use App\Models\User;

class ZazuHelperService
{
    public function attention(User $user, Business $business, string $routeName): array
    {
        if (!in_array($routeName, ['dashboard', 'work.index', 'purchasing.index', 'finance.index', 'onboarding.index'], true)) {
            return [];
        }

        $items = [];
        $can = fn (string $permission): bool => app(PermissionService::class)->allows($permission, $user, $business);

        if ($user->businesses()->whereKey($business->id)->wherePivot('role', 'owner')->exists()) {
            if (!$business->catalogue_setup_completed_at || !$business->business_setup_completed_at) {
                $items[] = [
                    'title' => 'Workspace setup is still open',
                    'copy' => 'Finish or deliberately defer the remaining setup steps so the workspace reflects your operating context.',
                    'href' => route('onboarding.index'),
                    'link' => 'Review setup',
                ];
            }
        }

        if ($can('work.view')) {
            $overdue = EventPreparationItem::query()
                ->where('business_id', $business->id)
                ->whereIn('status', ['open', 'blocked'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->startOfDay())
                ->whereHas('event', fn ($query) => $query
                    ->where('business_id', $business->id)
                    ->whereNotIn('status', Event::TERMINAL_STATUSES))
                ->count();

            if ($overdue > 0) {
                $items[] = [
                    'title' => $overdue.' preparation item'.($overdue === 1 ? '' : 's').' overdue',
                    'copy' => 'Review the affected jobs and clear or re-plan the outstanding operational work.',
                    'href' => route('work.index', ['filter' => 'overdue']),
                    'link' => 'Open overdue work',
                ];
            }
        }

        if ($can('purchasing.view')) {
            $openPurchases = PurchaseOrder::query()
                ->where('business_id', $business->id)
                ->whereIn('status', ['draft', 'sent', 'ordered'])
                ->count();

            if ($openPurchases > 0) {
                $items[] = [
                    'title' => $openPurchases.' purchase order'.($openPurchases === 1 ? '' : 's').' still open',
                    'copy' => 'Check outstanding purchasing commitments before they become execution-day surprises.',
                    'href' => route('purchasing.index'),
                    'link' => 'Review purchasing',
                ];
            }
        }

        if ($can('work.view')) {
            $drafts = Event::query()
                ->where('business_id', $business->id)
                ->where('status', 'draft')
                ->count();

            if ($drafts > 0) {
                $items[] = [
                    'title' => $drafts.' job'.($drafts === 1 ? '' : 's').' still in draft',
                    'copy' => 'Confirm the operational state when the job is ready to move beyond planning.',
                    'href' => route('work.index', ['filter' => 'draft']),
                    'link' => 'Open draft work',
                ];
            }
        }

        return array_slice($items, 0, 4);
    }
}
