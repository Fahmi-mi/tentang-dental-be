<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reservations', 'updated_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
        
        if (Schema::hasColumn('reservations', 'notes')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }

        if (Schema::hasColumn('patient_medical_histories', 'updated_at')) {
            Schema::table('patient_medical_histories', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }

        if (Schema::hasColumn('patient_dental_histories', 'updated_at')) {
            Schema::table('patient_dental_histories', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }

        if (Schema::hasColumn('galleries', 'updated_at')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('reservations', 'updated_at')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
        
        if (!Schema::hasColumn('reservations', 'notes')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->text('notes')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('patient_medical_histories', 'updated_at')) {
            Schema::table('patient_medical_histories', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }

        if (!Schema::hasColumn('patient_dental_histories', 'updated_at')) {
            Schema::table('patient_dental_histories', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }

        if (!Schema::hasColumn('galleries', 'updated_at')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }
    }
};
