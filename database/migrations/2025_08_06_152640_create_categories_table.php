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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Nama kategori
            $table->string('slug')->unique();       // Slug untuk URL
            $table->text('description')->nullable(); // Deskripsi kategori
            $table->boolean('is_active')->default(true); // Status aktif/tidak
            $table->string('image')->nullable();    // Gambar kategori (opsional)
            $table->timestamps();                   // created_at dan updated_at
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
