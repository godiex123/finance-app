<?php
 namespace App\Services;

 use App\Models\Transaction;
 use Illuminate\Database\Eloquent\Collection;

 class TransactionService
 {

     public function getAllTransactions(): Collection
     {
         return Transaction::with(['budgetedIncome', 'budgetedExpense'])->get();
     }

     /**
      * @throws \Exception
      */
     public function store(array $data)
    {
        try {
            return Transaction::create($data);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
 }
