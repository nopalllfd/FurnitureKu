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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID Pembeli
        $table->string('order_code')->unique(); // Kode unik: INV/2025/XYZ
        $table->unsignedBigInteger('total_amount'); // Total harga pesanan
        $table->text('shipping_address');
        $table->enum('status', ['pending', 'paid', 'shipped', 'completed', 'cancelled'])->default('pending');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
