<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopupBankTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topup_bank_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('topup_order_id');
            $table->integer('recipient_bank_id');
            $table->string('sender_bank_name');
            $table->string('sender_account_name');
            $table->string('sender_account_number');
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
        Schema::dropIfExists('topup_bank_transfers');
    }
}
