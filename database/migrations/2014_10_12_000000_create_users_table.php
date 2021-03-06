<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->double('balance')->default(0);
            $table->integer('status')->default(1);
            $table->string('image_name')->nullable()->default('default.jpg');
            $table->string('fcm_token_android')->nullable();
            $table->string('fcm_token_ios')->nullable();
            $table->string('jwt_token')->nullable();
            $table->string('pin_pattern')->nullable();
            $table->string('role')->nullable()->default('User');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
