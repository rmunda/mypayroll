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
        Schema::create('weekly_off_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // e.g. "5 Day Week", "6 Day Week"
            $table->boolean('monday')->default(true);
            $table->boolean('tuesday')->default(true);
            $table->boolean('wednesday')->default(true);
            $table->boolean('thursday')->default(true);
            $table->boolean('friday')->default(true);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->enum('saturday_type', [
                'working',
                'half_day',
                'alternate_1_3',    // 1st and 3rd saturday
                'alternate_2_4',    // 2nd and 4th saturday
                'non_working',
            ])->default('non_working');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_off_rules');
    }
};
