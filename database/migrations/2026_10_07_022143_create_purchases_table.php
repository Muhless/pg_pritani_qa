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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->onDelete('restrict');
            $table->foreignId('item_type_id')->constrained()->onDelete('restrict');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('price_per_kg', 10, 2);
            $table->decimal('moisture_deduction', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->date('purchase_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
