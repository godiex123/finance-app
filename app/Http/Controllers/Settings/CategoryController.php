<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CategoryController extends Controller
{

    public function __construct(
        protected CategoryService $categoryService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        return Inertia::render('settings/Category', [
            'categories' => $this->categoryService->getAll(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->categoryService->store(
            $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', 'max:10']
            ])
        );
        return to_route('category.index')->with('success', 'Categoría creada con éxito.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $this->categoryService->update(
            $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'type' => ['required', 'string', 'max:10']
            ]),
            $id
        );
        return to_route('category.index')->with('success', 'Categoría modificada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->categoryService->delete($id);
        return to_route('category.index')->with('success', 'Categoría eliminada con éxito.');
    }
}
