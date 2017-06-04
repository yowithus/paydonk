<?php

use Illuminate\Database\Seeder;

use Faker\Factory;

class SenderBanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	DB::table('sender_banks')->insert([
    		[
	            'name'  			=> 'BCA',
	            'image_name' 		=> 'bca.png',
	            'status' 			=> 1
            ],
            [
            	'name'  			=> 'Mandiri',
	            'image_name' 		=> 'mandiri.png',
	            'status' 			=> 1
            ],
        ]);
    }
}
