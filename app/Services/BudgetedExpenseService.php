<?php

namespace App\Services;

use App\Models\BudgetedExpense;
use Illuminate\Database\Eloquent\Collection;

class BudgetedExpenseService
{
    public function getExpensesByBudgetId(int $budgetId): Collection
    {
        return BudgetedExpense::where('budget_id', $budgetId)->get();
    }
}
