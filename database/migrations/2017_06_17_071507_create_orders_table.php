<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('product_code');
            $table->string('reference_id');
            $table->string('customer_number')->nullable();
            $table->double('product_price');
            $table->double('admin_fee');
            $table->double('order_amount');
            $table->integer('order_status');
            $table->double('discount_amount');
            $table->double('payment_amount');
            $table->integer('payment_status');
            $table->string('payment_method');
            $table->string('payment_external_id')->nullable();
            $table->integer('promo_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
