<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\FixedIncome;
use App\Models\FixedExpense;
use App\Models\BudgetedIncome;
use App\Models\BudgetedExpense;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Constants\Months;

class BudgetService
{
    public function budgetDashboard(Request $request, int $userId): array
    {
        $date = now();
        $currentYear = $date->year;
        $nextYear = $currentYear + 1;
        $showBudgetButton = $date->month == 12
            ? !Budget::where('year', $nextYear)->exists()
            : !Budget::where('year', $currentYear)->exists();
        $year = $date->month == 12 ? $nextYear : $currentYear;
        $months = Budget::select(['id', 'month as name', 'year'])->where('year', $year)->where('user_id', $userId)->get();

        if ($request->all()) {
            $budgetId = $request->get('bid');
            $budgetedIncomes = BudgetedIncome::withSum('transaction as real_amount', 'amount')->where('budget_id', $budgetId)->get();
            $totalBudgetedIncomes = $budgetedIncomes->sum('amount');
            $budgetedExpenses = BudgetedExpense::withSum('transaction as real_amount', 'amount')->where('budget_id', $budgetId)->get();
            $totalBudgetedExpenses = $budgetedExpenses->sum('amount');
            $incomeCategories = Category::where('type', 'income')->get();
            $expenseCategories = Category::where('type', 'expense')->get();
            $transactions = Transaction::with(['budgetedIncome', 'budgetedExpense'])
                                       ->where(function ($query) use ($budgetId) {
                                           $query->whereHas('budgetedIncome', function ($q) use ($budgetId) {
                                               $q->where('budget_id', $budgetId);
                                           })->orWhereHas('budgetedExpense', function ($q) use ($budgetId) {
                                               $q->where('budget_id', $budgetId);
                                           });
                                       })
                                       ->get();
            $totalIncomeTransactions = $transactions->filter(function ($t) {
                return (
                    $t->budgetedIncome?->category?->type === 'income' ||
                    $t->budgetedExpense?->category?->type === 'income'
                );
            })->sum('amount');
            $totalExpenseTransactions = $transactions->filter(function ($t) {
                return (
                    $t->budgetedIncome?->category?->type === 'expense' ||
                    $t->budgetedExpense?->category?->type === 'expense'
                );
            })->sum('amount');
        } else {
            $budgetId = 0;
            $budgetedIncomes = [];
            $totalBudgetedIncomes = 0;
            $budgetedExpenses = [];
            $totalBudgetedExpenses = 0;
            $incomeCategories = [];
            $expenseCategories = [];
            $transactions = [];
            $totalIncomeTransactions = 0;
            $totalExpenseTransactions = 0;
        }

        return [
            'showBudgetButton' => $showBudgetButton,
            'year' => $year,
            'budgetId' => $budgetId,
            'months' => $months,
            'budgetedIncomes' => $budgetedIncomes,
            'totalBudgetedIncomes' => $totalBudgetedIncomes,
            'budgetedExpenses' => $budgetedExpenses,
            'totalBudgetedExpenses' => $totalBudgetedExpenses,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'transactions' => $transactions,
            'totalIncomeTransactions' => $totalIncomeTransactions,
            'totalExpenseTransactions' => $totalExpenseTransactions,
        ];
    }




    public function budgetMonths(int $userId, int $year): Collection
    {
        return Budget::select(['id', 'month as name', 'year'])->where('year', $year)->where('user_id', $userId)->get();
    }

    public function getAllIncomes(int $budgetId): Collection
    {
        return BudgetedIncome::where('budget_id', $budgetId)->get();
    }

    public function showBudgetButton(): array
    {
        $date = now();
        $currentYear = $date->year;
        $nextYear = $currentYear + 1;

        if ($date->month == 12) {
            return [
                !Budget::where('year', $nextYear)->exists(),
                $nextYear,
            ];
        } else {
            return [
                !Budget::where('year', $currentYear)->exists(),
                $currentYear,
            ];
        }
    }

    public function generateMonthlyBudgets(int $userId, int $year): void
    {
        $date = now();
        $currentMonth = $date->month;
        $currentDay = $date->day;
        $data = [
            'user_id' => $userId,
            'year' => $year,
        ];

        $i = $currentDay > 5 ? $currentMonth : $currentMonth - 1;
        $months = array_slice(Months::LIST, $i);

        foreach ($months as $month) {
            $data['month'] = $month;

            try {
                $budget = Budget::create($data);
                $this->copyFixedIncomes($userId, $budget->id);
                $this->copyFixedExpenses($userId, $budget->id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage());
            }
        }
    }

    public function copyFixedIncomes(int $userId, int $budgetId): void
    {
        $incomes = FixedIncome::where('user_id', $userId)->whereNull('deleted_at')->get();
        if (!$incomes->isEmpty()) {
            foreach ($incomes as $income) {
                $data = [
                    'budget_id' => $budgetId,
                    'category_id' => $income->category_id,
                    'amount' => $income->amount,
                    'name' => $income->name,
                    'comment' => $income->comment,
                ];
                BudgetedIncome::create($data);
            }
        }
    }

    public function copyFixedExpenses(int $userId, int $budgetId): void
    {
        $expenses = FixedExpense::where('user_id', $userId)->whereNull('deleted_at')->get();
        if (!$expenses->isEmpty()) {
            foreach ($expenses as $expense) {
                $data = [
                    'budget_id' => $budgetId,
                    'category_id' => $expense->category_id,
                    'amount' => $expense->amount,
                    'name' => $expense->name,
                    'is_essential' => $expense->is_essential,
                    'comment' => $expense->comment,
                ];
                BudgetedExpense::create($data);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function storeVariableIncome(array $data)
    {
        if ($this->validateIncome($data)) {
            throw new Exception(sprintf('El ingreso "%s" ya existe.', $data['name']));
        }

        try {
            return BudgetedIncome::create($data);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function validateIncome(array $data): bool
    {
        return (bool) BudgetedIncome::where('amount', $data['amount'])
                                    ->where('name', $data['name'])
                                    ->where('budget_id', $data['budget_id'])
                                    ->where('category_id', $data['category_id'])->first();
    }

    public function storeVariableExpense(array $data)
    {
        if ($this->validateExpense($data)) {
            throw new Exception(sprintf('El gasto "%s" ya existe.', $data['name']));
        }

        try {
            return BudgetedExpense::create($data);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function validateExpense(array $data): bool
    {
        return (bool) BudgetedExpense::where('amount', $data['amount'])
                                  ->where('name', $data['name'])
                                  ->where('budget_id', $data['budget_id'])
                                  ->where('category_id', $data['category_id'])->first();
    }

    public function getSumAllIncomes(int $budgetId): float
    {
        return BudgetedIncome::where('budget_id', $budgetId)->sum('amount');
    }

    public function getSumAllEXpenses(int $budgetId): float
    {
        return BudgetedExpense::where('budget_id', $budgetId)->sum('amount');
    }

    public function getBudgeted(string $type, int $budgetId): Collection
    {
        if ($type === 'income') {
            return BudgetedIncome::where('budget_id', $budgetId)->get();
        } else {
            return BudgetedExpense::where('budget_id', $budgetId)->get();
        }
    }
}
