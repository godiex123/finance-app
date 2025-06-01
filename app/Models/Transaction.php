<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use softDeletes;
    protected $guarded = [];

    public function budgetedIncome():belongsTo
    {
        return $this->belongsTo(BudgetedIncome::class, 'budgeted_income_id', 'id');;
    }

    public function budgetedExpense():belongsTo
    {
        return $this->belongsTo(BudgetedExpense::class, 'budgeted_expense_id', 'id');
    }
}
