<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function transactions(): hasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function fixedIncomes(): hasMany
    {
        return $this->hasMany(FixedIncome::class);
    }

    public function fixedExpenses(): hasMany
    {
        return $this->hasMany(FixedExpense::class);
    }
}
