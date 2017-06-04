<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopupOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topup_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('reference_id');
            $table->double('order_amount');
            $table->integer('order_status');
            $table->double('payment_amount');
            $table->integer('payment_status');
            $table->string('payment_method');
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
        Schema::dropIfExists('topup_orders');
    }
}
