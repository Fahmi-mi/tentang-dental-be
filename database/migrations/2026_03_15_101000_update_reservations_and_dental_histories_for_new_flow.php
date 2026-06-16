<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'patient_category')) {
                $table->enum('patient_category', ['new', 'existing'])
                    ->default('new')
                    ->after('patient_id');
            }

            if (!Schema::hasColumn('reservations', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('reservation_date');
            }

            if (!Schema::hasColumn('reservations', 'age')) {
                $table->integer('age')->nullable()->after('birth_date');
            }
        });

        Schema::table('patient_dental_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_dental_histories', 'doctor_notes')) {
                $table->longText('doctor_notes')->nullable()->after('dental_checkup_frequency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_dental_histories', function (Blueprint $table) {
            if (Schema::hasColumn('patient_dental_histories', 'doctor_notes')) {
                $table->dropColumn('doctor_notes');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'age')) {
                $table->dropColumn('age');
            }

            if (Schema::hasColumn('reservations', 'birth_date')) {
                $table->dropColumn('birth_date');
            }

            if (Schema::hasColumn('reservations', 'patient_category')) {
                $table->dropColumn('patient_category');
            }
        });
    }
};
