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
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('description')->nullable();
            $table->double('amount');
            $table->date('date');
            $table->enum('type',['INCOME', 'SPENDING', 'LOAN', 'OWE']); // ENUM
            $table->boolean('loan_owe_ok')->default(true);
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('parent_budget_id')->nullable()->constrained('budgets')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->enum('frequency',['REGULAR','UNIQUE','PERIODIC']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
