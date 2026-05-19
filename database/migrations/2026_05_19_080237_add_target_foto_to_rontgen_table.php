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
            $table->string('target_foto')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('rontgen', function (Blueprint $table) {
            $table->dropColumn('target_foto');
        });
    }
};
