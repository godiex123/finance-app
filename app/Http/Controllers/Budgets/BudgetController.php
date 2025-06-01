<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\Models\BudgetedExpense;
use App\Models\BudgetedIncome;
use App\Models\Category;
use App\Services\BudgetService;
use App\Services\BudgetedIncomeService;
use App\Services\BudgetedExpenseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mockery\Exception;

class BudgetController extends Controller
{
    private int $userId;

    public function __construct(
        protected BudgetService $budgetService,
        protected BudgetedIncomeService $budgetedIncome,
        protected BudgetedExpenseService $budgetedExpense,
        protected TransactionService $transactionService,
    ){
        $this->userId = Auth()->id();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('Dashboard',
            $this->budgetService->budgetDashboard($request, $this->userId)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            // Generate months
            $this->budgetService->generateMonthlyBudgets(auth()->id(), $request->get('year'));
            return to_route('budget.index')->with('success', 'Presupuesto generado con éxito.');
        } catch (\throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeVariableIncome(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'budget_id' => 'required',
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|numeric',
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $this->budgetService->storeVariableIncome($validated);
            return redirect()->back()->with('success', 'Ingreso variable creado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeVariableExpense(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'budget_id' => 'required',
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|numeric',
            'is_essential' => ['sometimes', 'boolean'],
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $this->budgetService->storeVariableExpense($validated);
            return to_route('budget.index')->with('success', 'Gasto variable creado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }
}
