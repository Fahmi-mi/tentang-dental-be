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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('nickname', 100)->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->integer('age')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address');
            $table->string('village', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('phone', 20)->unique();
            $table->string('occupation', 100)->nullable();
            $table->string('parent_name', 150)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
