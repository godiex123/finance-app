<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetedExpense;
use App\Models\FixedExpense;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use App\Constants\Months;

class FixedExpenseService
{
    public function getAllFixedExpenses(int $userId): Collection
    {
        return FixedExpense::where('user_id', $userId)->with('category')->get();
    }

    public function getExpenseCategories(): Collection
    {
        return Category::where('type', 'expense')->get();
    }

    /**
     * @throws Exception
     */
    public function store(array $data)
    {
        if ($this->validateFixedExpense($data)) {
            throw new Exception(sprintf('El gasto "%s" ya existe.', $data['name']));
        }

        try {
            if ($id = FixedExpense::create($data)) {
                $this->insertIntoBudgetedExpenses($data);
            }
            return $id;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function update(array $data, int $id)
    {
        try {
            $fixedExpense = FixedExpense::find($id);
            $oldAmount = $fixedExpense->amount;
            $oldName = $fixedExpense->name;
            $oldCategoryId = $fixedExpense->category_id;

            if ($id = $fixedExpense->update($data)) {
                $this->updateBudgetedExpenses(
                    $fixedExpense->user_id,
                    $oldAmount,
                    $oldName,
                    $oldCategoryId,
                    $data);
            }

            return $id;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function delete(int $id)
    {
        try {
            $fixedExpense = FixedExpense::find($id);
            return $fixedExpense->delete($id);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function validateFixedExpense(array $data): bool
    {
        return (bool) FixedExpense::where('amount', $data['amount'])
                                 ->where('name', $data['name'])
                                 ->where('user_id', $data['user_id'])
                                 ->where('category_id', $data['category_id'])->first();
    }

    public function insertIntoBudgetedExpenses(array $data): void
    {
        $date = now();
        $currentYear = $date->year;
        $currentMonth = $date->month;
        $currentDay = $date->day;
        $hasBudgets = Budget::where('user_id', $data['user_id'])
                            ->where('year', $currentYear)
                            ->get();
        unset($data['user_id']);

        if ($hasBudgets) {
            $i = $currentDay > 9 ? $currentMonth : $currentMonth - 1;
            foreach (array_slice(Months::LIST, $i) as $month) {
                foreach ($hasBudgets as $bg) {
                    if ($bg->month === $month) {
                        $data['budget_id'] = $bg->id;
                        BudgetedExpense::create($data);
                    }
                }
            }
        }
    }

    public function updateBudgetedExpenses(int $userId, float $amount, string $name, int $category_id, array $newData): void
    {
        $date = now();
        $currentYear = $date->year;
        $currentMonth = $date->month;
        $currentDay = $date->day;
        $hasBudgets = Budget::where('user_id', $userId)
                            ->where('year', $currentYear)
                            ->get();

        if ($hasBudgets) {
            $i = $currentDay > 5 ? $currentMonth : $currentMonth - 1;
            foreach (array_slice(Months::LIST, $i) as $month) {
                foreach ($hasBudgets as $bg) {
                    if ($bg->month === $month) {
                        $budgeted = BudgetedExpense::where('budget_id', $bg->id)
                            ->where('category_id', $category_id)
                            ->where('amount', $amount)
                            ->where('name', $name)
                            ->first();

                        if ($budgeted) {
                            $budgeted->update($newData);
                        }
                    }
                }
            }
        }
    }
}
