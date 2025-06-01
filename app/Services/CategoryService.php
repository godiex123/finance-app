<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::all();
    }

    /**
     * @throws ValidationException
     */
    public function store(array $data): Category
    {
        if ($this->categoryExist($data['name'], $data['type'])) {
            throw ValidationException::withMessages([
                'msg' => sprintf('El nombre "%s" ya existe.', $data['name']),
            ]);
        }
        return Category::create($data);
    }

    /**
     * @throws ValidationException
     */
    public function update(array $data, int $id): bool
    {
        $category = Category::where('id', $id)->first();
        $validation = Category::where('name', $data['name'])->where('type', $data['type'])->where('id', '!=', $id)->get();
        if ($validation->count() > 0) {
            throw ValidationException::withMessages([
                'msg' => sprintf('El nombre "%s" y tipo "%s" ya están siendo usados en otro registro.', $data['name'], $data['type'] === 'income' ? 'Ingreso' : 'Gasto'),
            ]);
        }
        return $category->update($data);
    }

    public function delete(int $id): bool
    {
        $category = Category::find($id);
        return $category->delete();
    }

    public function categoryExist(string $name, string $type): bool
    {
        return (bool) Category::where('name', $name)->where('type', $type)->first();
    }
}
