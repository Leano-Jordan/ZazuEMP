<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\FinanceExpense;
use Illuminate\Database\Eloquent\Builder;

class ExpenseSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = FinanceExpense::query()
            ->where('business_id', $business->id)
            ->with(['supplier', 'event', 'purchaseOrder']);

        $this->text($query, $term, ['description', 'reference', 'notes', 'currency']);
        $this->applyStatusAndDate($query, $status, $from, $to, 'expense_date');

        return $query->latest('expense_date')->limit(10)->get()->map(fn (FinanceExpense $expense) => [
            'type' => 'expense',
            'type_label' => 'Expense',
            'title' => $expense->description,
            'meta' => trim(($expense->reference ?: 'Expense'). ' · '.($expense->event?->name ?: 'Business expense')),
            'status' => $expense->status,
            'date' => $expense->expense_date?->format('d M Y'),
            'href' => route('finance.index'),
            'sort_date' => ($expense->expense_date ?: $expense->created_at)?->toDateString(),
        ])->all();
    }
}
