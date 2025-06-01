<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedExpense extends Model
{
    use SoftDeletes;

    protected $table = 'fixed_expenses';
    protected $guarded = [];

    protected $attributes = [
        'is_essential' => false,
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
