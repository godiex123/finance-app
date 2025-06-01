<?php

namespace App\Services;

use App\Models\BudgetedIncome;
use Illuminate\Database\Eloquent\Collection;

class BudgetedIncomeService
{
    public function getIncomesByBudgetId(int $budgetId): Collection
    {
        return BudgetedIncome::where('budget_id', $budgetId)->get();
    }
}
