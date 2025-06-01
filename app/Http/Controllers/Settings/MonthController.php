<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\MonthService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonthController extends Controller
{
    public function __construct(
        protected MonthService $monthService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        return Inertia::render('settings/Month', [
            'months' => $this->monthService->getAllMonths(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|min:1|max:12',
            'name' => 'required|string|max:15',
        ]);

        try {
            $this->monthService->store($validated);
            return to_route('month.index')->with('success', 'Mes creado con éxito.');
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
            'id' => 'required|integer|min:1|max:12',
            'name' => 'required|string|max:15',
        ]);

        try {
            $this->monthService->update($validated, $id);
            return to_route('month.index')->with('success', 'Mes modificado con éxito.');
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
            $this->monthService->delete($id);
            return to_route('month.index')->with('success', 'Mes eliminado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }
}
