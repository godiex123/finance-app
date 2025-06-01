<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgeted_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('name', 100);
            $table->boolean('is_essential')->nullable();
            $table->text('comment')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgeted_expenses');
    }
};
