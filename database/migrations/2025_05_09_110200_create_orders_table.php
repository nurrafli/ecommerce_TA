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
            $table->string('order_id', 32)->unique();
            $table->bigInteger('user_id')->unsigned();
            $table->integer('subtotal');
            $table->integer('discount')->default(0);
            $table->integer('tax');
            $table->integer('total');
            $table->string('name');
            $table->string('phone');
            $table->string('locality');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('landmark')->nullable();
            $table->string('zip');
            $table->string('type')->default('home');
            $table->enum('status',['pending','processing','completed','cancelled'])->default('pending');
            $table->boolean('is_shipping_different')->default(false);
            $table->date('delivered_date')->nullable();
            $table->date('canceled_date')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
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


