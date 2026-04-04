<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('rontgen', function (Blueprint $table) {
        // Hapus xray_image karena sudah pindah ke examination_images
        $table->dropColumn('xray_image');

        // Tambah doctor_id
        $table->foreignId('doctor_id')
              ->nullable()
              ->after('patient_id')
              ->constrained('doctors')
              ->onDelete('set null');

        // Tambah status
        $table->enum('status', [
            'menunggu',
            'di_dalam_ruangan',
            'perlu_upload_foto',
            'selesai'
        ])->default('menunggu')->after('detail');
    });
}

public function down()
{
    Schema::table('rontgen', function (Blueprint $table) {
        $table->string('xray_image')->nullable();
        $table->dropForeign(['doctor_id']);
        $table->dropColumn(['doctor_id', 'status']);
    });
}
};
