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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Casual Leave
            $table->string('code')->unique();          // casual
            $table->string('color')->default('info');  // badge color
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_document')->default(false); // sick leave needs medical cert
            $table->boolean('is_accrued')->default(false);        // earned leave accrues
            $table->decimal('max_days_per_year', 5, 1)->default(0);
            $table->decimal('max_days_per_request', 5, 1)->default(0); // max days in one request
            $table->integer('min_notice_days')->default(0);        // advance notice required
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
