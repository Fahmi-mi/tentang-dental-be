<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_images', function (Blueprint $table) {
            $table->foreignId('dental_examination_id')
                  ->nullable()
                  ->after('rontgen_id')
                  ->constrained('dental_examinations')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('examination_images', function (Blueprint $table) {
            $table->dropForeign(['dental_examination_id']);
            $table->dropColumn('dental_examination_id');
        });
    }
};