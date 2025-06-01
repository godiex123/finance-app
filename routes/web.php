<?php

use App\Http\Controllers\Budgets\BudgetController;
use App\Http\Controllers\Movements\TransactionController;
use App\Http\Controllers\Templates\FixedExpenseController;
use App\Http\Controllers\Templates\FixedIncomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/transaction', [TransactionController::class, 'index'])->middleware(['auth', 'verified'])->name('transaction.index');

Route::resource('/fixed-income', FixedIncomeController::class)->middleware(['auth', 'verified']);

Route::resource('/fixed-expense', FixedExpenseController::class)->middleware(['auth', 'verified']);

Route::resource('/budget', BudgetController::class)->middleware(['auth', 'verified']);
Route::post('/budget/storeVariableIncome', [BudgetController::class, 'storeVariableIncome'])->middleware(['auth', 'verified']);
Route::post('/budget/storeVariableExpense', [BudgetController::class, 'storeVariableExpense'])->middleware(['auth', 'verified']);

Route::post('/transaction/store', [TransactionController::class, 'store'])->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
