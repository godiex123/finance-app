<?php
namespace App\Services;

use App\Models\Year;
use Exception;
use Illuminate\Validation\ValidationException;

class YearService
{
    public function getAllYears(): \Illuminate\Database\Eloquent\Collection
    {
        return Year::all();
    }

    /**
     * @throws Exception
     */
    public function store(array $data): Year
    {
        if ($this->yearExists($data['id'])) {
            throw new Exception(sprintf('El año "%s" ya existe.', $data['id']));
        }

        try {
            return Year::create($data);
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
            $year = Year::find($id);
            return $year->delete();
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function yearExists(int $id): bool
    {
        return (bool) Year::where('id', $id)->first();
    }
}
