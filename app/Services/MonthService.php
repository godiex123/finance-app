<?php
namespace App\Services;

use App\Models\Month;
use Exception;
use Illuminate\Validation\ValidationException;

class MonthService
{
    public function getAllMonths(): \Illuminate\Database\Eloquent\Collection
    {
        return Month::all();
    }

    /**
     * @throws Exception
     */
    public function store(array $data): Month
    {
        if ($this->monthNumberExists($data['id'])) {
            throw new Exception(sprintf('El mes "%s" ya existe.', $data['id']));
        }

        if ($this->monthNameExists($data['name'])) {
            throw new Exception(sprintf('El mes "%s" ya existe.', $data['name']));
        }

        try {
            return Month::create($data);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function update(array $data, int $id): bool
    {
        if ($id !== $data['id'] && $this->monthNumberExists($data['id'])) {
            throw new Exception(sprintf('El mes "%s" ya existe.', $data['id']));
        }

        if ($this->monthNameExists($data['name'])) {
            throw new Exception(sprintf('El mes "%s" ya existe.', $data['name']));
        }

        try {
            $month = Month::find($id);
            return $month->update($data);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        try {
            $month = Month::find($id);
            return $month->delete();
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

    }

    public function monthNumberExists(int $id): bool
    {
        return (bool) Month::where('id', $id)->first();
    }
    public function monthNameExists(string $name): bool
    {
        return (bool) Month::where('name', $name)->first();
    }
}
