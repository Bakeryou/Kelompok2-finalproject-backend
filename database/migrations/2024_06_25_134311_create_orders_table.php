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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cart_id')->nullable()->constrained('carts')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->enum('order_type', ['Pickup', 'Delivery'])->default('Pickup');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('customer_postal_code');
            $table->string('customer_city');
            $table->text('notes')->nullable();
            $table->double('subtotal')->default(0);
            $table->double('tax')->default(0);
            $table->double('shipping')->default(0);
            $table->double('total')->default(0);
            $table->enum('status_payment', ['Unpaid', 'Paid'])->default('Unpaid');
            $table->enum('status', ['Pending', 'Process', 'Completed', 'Canceled'])->default('Pending');
            $table->string('snap_token')->nullable();
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
