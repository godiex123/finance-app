<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use App\Services\FixedExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FixedExpenseController extends Controller
{

    private int $userId;
    public function __construct(
        protected FixedExpenseService $fixedExpenseService
    ){
        $this->userId = Auth()->id();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        return Inertia::render('templates/FixedExpense', [
            'categories' => $this->fixedExpenseService->getExpenseCategories(),
            'fixedExpenses' => $this->fixedExpenseService->getAllFixedExpenses($this->userId)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|numeric',
            'is_essential' => ['sometimes', 'boolean'],
            'comment' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = $this->userId;

        try {
            $this->fixedExpenseService->store($validated);
            return to_route('fixed-expense.index')->with('success', 'Gasto fijo creado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|numeric',
            'is_essential' => ['sometimes', 'boolean'],
            'comment' => 'nullable|string|max:255',
        ]);

        try {
            $this->fixedExpenseService->update($validated, $id);
            return to_route('fixed-expense.index')->with('success', 'Gasto fijo modificado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->fixedExpenseService->delete($id);
            return to_route('fixed-expense.index')->with('success', 'Gasto fijo eliminado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }
}
