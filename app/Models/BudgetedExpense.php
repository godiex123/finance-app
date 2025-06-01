<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetedExpense extends Model
{
    protected $table = 'budgeted_expenses';
    protected $guarded = [];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transaction(): hasMany
    {
        return $this->hasMany(Transaction::class, 'budgeted_expense_id');
    }
}
