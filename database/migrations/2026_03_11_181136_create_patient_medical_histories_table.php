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
        Schema::create('patient_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->boolean('has_allergy')->default(false);
            $table->text('allergy_detail')->nullable();
            $table->boolean('has_systemic_disease')->default(false);
            $table->text('systemic_disease_detail')->nullable();
            $table->boolean('undergoing_treatment')->default(false);
            $table->text('treatment_detail')->nullable();
            $table->boolean('ever_hospitalized')->default(false);
            $table->text('hospitalized_reason')->nullable();
            $table->boolean('smoking_or_alcohol')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medical_histories');
    }
};
