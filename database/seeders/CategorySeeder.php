<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => 'Salario',
                'type' => 'income',
            ],
            [
                'name' => 'Bono',
                'type' => 'income',
            ],
            [
                'name' => 'Reembolso',
                'type' => 'income',
            ],

            [
                'name' => 'Ingreso extra',
                'type' => 'income'
            ],
            [
                'name' => 'Vivienda',
                'type' => 'expense',
            ],
            [
                'name' => 'Alimentación',
                'type' => 'expense',
            ],
            [
                'name' => 'Transporte',
                'type' => 'expense',
            ],
            [
                'name' => 'Personales',
                'type' => 'expense',
            ],
            [
                'name' => 'Educación',
                'type' => 'expense',
            ],
            [
                'name' => 'Ocio',
                'type' => 'expense',
            ],
            [
                'name' => 'Finanzas',
                'type' => 'expense',
            ],
            [
                'name' => 'Familia',
                'type' => 'expense',
            ],
        ]);
    }
}
