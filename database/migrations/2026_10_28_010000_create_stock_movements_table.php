<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 10);
            $table->decimal('amount', 10, 2);
            $table->decimal('quantity_before', 10, 2);
            $table->decimal('quantity_after', 10, 2);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['item_type_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
