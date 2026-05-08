<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rontgen_id')->constrained('rontgen')->onDelete('cascade');
            $table->integer('visit_number')->default(1);
            $table->date('visit_date')->nullable();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('planning')->nullable();
            $table->text('treatment')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_examinations');
    }
};