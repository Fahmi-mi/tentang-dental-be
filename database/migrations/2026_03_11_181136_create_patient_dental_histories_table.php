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
        Schema::create('patient_dental_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->boolean('frequent_tooth_pain')->default(false);
            $table->text('tooth_pain_detail')->nullable();
            $table->boolean('bleeding_gums')->default(false);
            $table->boolean('ever_dental_treatment')->default(false);
            $table->text('dental_treatment_detail')->nullable();
            $table->enum('brushing_frequency', ['1', '2', 'more_than_2'])->nullable();
            $table->boolean('use_floss_or_mouthwash')->default(false);
            $table->boolean('bad_habits')->default(false);
            $table->text('bad_habits_detail')->nullable();
            $table->boolean('ever_braces')->default(false);
            $table->integer('braces_years')->nullable();
            $table->boolean('root_canal_treatment')->default(false);
            $table->text('root_canal_detail')->nullable();
            $table->boolean('dentures')->default(false);
            $table->boolean('routine_checkup')->default(false);
            $table->enum('dental_checkup_frequency', ['6_months', '1_year', 'more_than_1_year', 'never'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_dental_histories');
    }
};
