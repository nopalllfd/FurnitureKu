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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // relasi ke kategori
            $table->string('name');                  // Nama produk
            $table->string('slug')->unique();        // Slug produk
            $table->text('description');             // Deskripsi produk
            $table->integer('price');                // Harga
            $table->integer('stock')->default(0);    // Stok produk
            $table->string('image')->nullable();     // URL gambar
            $table->boolean('is_active')->default(true); // Status aktif atau tidak
            $table->timestamps();                    // created_at dan updated_at
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
