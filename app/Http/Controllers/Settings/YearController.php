<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\YearService;

class YearController extends Controller
{
    public function __construct(
        protected YearService $yearService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        return Inertia::render('settings/Year', [
            'years' => $this->yearService->getAllYears(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:2025', 'max:2100'],
        ]);

        try {
            $this->yearService->store($validated);
            return to_route('year.index')->with('success', 'Año creada con éxito.');
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
            $this->yearService->delete($id);
            return to_route('year.index')->with('success', 'Año eliminado con éxito.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                'error' => $th->getMessage()
            ])->withInput();
        }
    }
}
