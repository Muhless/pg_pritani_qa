<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('milling_processes', function (Blueprint $table) {
            $table->foreignId('rice_item_type_id')->nullable()->constrained('item_types')->restrictOnDelete();
            $table->foreignId('bran_item_type_id')->nullable()->constrained('item_types')->restrictOnDelete();
            $table->foreignId('husk_item_type_id')->nullable()->constrained('item_types')->restrictOnDelete();
            $table->foreignId('broken_rice_item_type_id')->nullable()->constrained('item_types')->restrictOnDelete();
            $table->unique('purchase_id');
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('milling_processes', function (Blueprint $table) {
            $table->dropUnique(['purchase_id']);
            $table->dropConstrainedForeignId('rice_item_type_id');
            $table->dropConstrainedForeignId('bran_item_type_id');
            $table->dropConstrainedForeignId('husk_item_type_id');
            $table->dropConstrainedForeignId('broken_rice_item_type_id');
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
    }
};