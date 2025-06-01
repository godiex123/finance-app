<?php

namespace App\Services;

use App\Constants\Months;
use App\Models\Budget;
use App\Models\BudgetedIncome;
use App\Models\FixedIncome;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class FixedIncomeService
{
    public function getAllFixedIncomes(int $userId): Collection
    {
        return FixedIncome::where('user_id', $userId)->with('category')->get();
    }

    public function getIncomeCategories(): Collection
    {
        return Category::where('type', 'income')->get();
    }

    /**
     * @throws Exception
     */
    public function store(array $data)
    {
        if ($this->validateFixedIncome($data)) {
            throw new Exception(sprintf('El ingreso "%s" ya existe.', $data['name']));
        }

        try {
            if ($id = FixedIncome::create($data)) {
                $this->insertIntoBudgetedIncomes($data);
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
            $fixedIncome = FixedIncome::find($id);
            $oldAmount = $fixedIncome->amount;
            $oldName = $fixedIncome->name;
            $oldCategoryId = $fixedIncome->category_id;

            if ($id = $fixedIncome->update($data)) {
                $this->updateBudgetedIncomes(
                    $fixedIncome->user_id,
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
            $fixedIncome = FixedIncome::find($id);
            return $fixedIncome->delete($id);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function validateFixedIncome(array $data): bool
    {
        return (bool) FixedIncome::where('amount', $data['amount'])
                                 ->where('name', $data['name'])
                                 ->where('user_id', $data['user_id'])
                                 ->where('category_id', $data['category_id'])->first();
    }

    public function insertIntoBudgetedIncomes(array $data): void
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
                        BudgetedIncome::create($data);
                    }
                }
            }
        }
    }

    public function updateBudgetedIncomes(int $userId, float $amount, string $name, int $category_id, array $newData): void
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
                        $budgeted = BudgetedIncome::where('budget_id', $bg->id)
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
