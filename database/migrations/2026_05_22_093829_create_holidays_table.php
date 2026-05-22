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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('name');               // e.g. "Diwali"
            $table->date('date');                 // the actual date
            $table->enum('type', [
                'national',                       // Republic Day, Independence Day
                'regional',                       // state specific
                'optional',                       // employee can choose
                'company',                        // company declared
            ])->default('national');
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(true); // paid or unpaid holiday
            $table->timestamps();

            // same date can exist in different financial years
            // but not twice in the same FY
            $table->unique(['financial_year_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
