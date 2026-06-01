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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // basic info
            $table->string('company_name');
            $table->string('logo')->nullable();
            $table->string('tagline')->nullable();

            // address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('country')->default('India');

            // contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // statutory
            $table->string('gstin')->nullable();
            $table->string('pan')->nullable();
            $table->string('cin')->nullable();
            $table->string('epf_registration_no')->nullable();
            $table->string('esic_registration_no')->nullable();
            $table->string('pt_registration_no')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
