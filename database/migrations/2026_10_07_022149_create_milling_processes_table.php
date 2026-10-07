<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('milling_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('restrict');
            $table->decimal('input_weight_kg', 10, 2);
            $table->decimal('rice_output_kg', 10, 2);
            $table->decimal('bran_output_kg', 10, 2)->default(0);
            $table->decimal('husk_output_kg', 10, 2)->default(0);
            $table->decimal('broken_rice_output_kg', 10, 2)->default(0);
            $table->decimal('yield_percentage', 5, 2);
            $table->dateTime('processed_at');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milling_processes');
    }
};
