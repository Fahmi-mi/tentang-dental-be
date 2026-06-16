<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_oral_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rontgen_id')->constrained('rontgen')->onDelete('cascade');
            $table->enum('face', ['symmetric', 'asymmetric'])->nullable();
            $table->enum('facial_skin_neck', ['normal', 'abnormal'])->nullable();
            $table->enum('lymph_nodes', ['palpable', 'not_palpable'])->nullable();
            $table->enum('temporomandibular_joint', ['normal', 'abnormal'])->nullable();
            $table->enum('muscle_mass', ['normal', 'abnormal'])->nullable();
            $table->enum('facial_swelling', ['present', 'absent'])->nullable();
            $table->enum('eyes_nose', ['normal', 'abnormal'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_oral_examinations');
    }
};