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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present','absent','half_day','on_leave','holiday','weekend']);
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('hours_worked')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->unique(['employee_id','date']);
            $table->index(['employee_id','date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
