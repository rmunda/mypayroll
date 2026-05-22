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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('label');           // FY 2025-26
            $table->date('start_date');        // 2025-04-01
            $table->date('end_date');          // 2026-03-31
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            // only one FY can be current at a time
            $table->unique('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
