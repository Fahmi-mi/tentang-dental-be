<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('examination_images', function (Blueprint $table) {
            $table->enum('image_phase', ['before', 'after'])
                  ->nullable()
                  ->after('image_type');
        });
    }

    public function down(): void
    {
        Schema::table('examination_images', function (Blueprint $table) {
            $table->dropColumn('image_phase');
        });
    }
};