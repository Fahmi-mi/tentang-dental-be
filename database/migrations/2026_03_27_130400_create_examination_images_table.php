<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rontgen_id')->constrained('rontgen')->onDelete('cascade');
            $table->string('image_path', 255);
            $table->text('image_type');
            $table->timestamp('created_at')->useCurrent();

            $table->index('rontgen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_images');
    }
};
