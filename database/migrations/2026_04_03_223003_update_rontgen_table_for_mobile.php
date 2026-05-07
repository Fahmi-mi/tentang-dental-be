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
        Schema::table('rontgen', function (Blueprint $table) {
            if (Schema::hasColumn('rontgen', 'xray_image')) {
                $table->dropColumn('xray_image');
            }

            if (!Schema::hasColumn('rontgen', 'doctor_id')) {
                $table->foreignId('doctor_id')
                    ->nullable()
                    ->after('patient_id')
                    ->constrained('doctors')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('rontgen', 'status')) {
                $table->enum('status', [
                    'menunggu',
                    'di_dalam_ruangan',
                    'perlu_upload_foto',
                    'selesai',
                ])->default('menunggu')->after('detail');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rontgen', function (Blueprint $table) {
            if (!Schema::hasColumn('rontgen', 'xray_image')) {
                $table->string('xray_image')->nullable();
            }

            if (Schema::hasColumn('rontgen', 'doctor_id')) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            }

            if (Schema::hasColumn('rontgen', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
