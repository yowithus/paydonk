<?php

use Illuminate\Database\Seeder;

use Faker\Factory;

class RecipientBanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	DB::table('recipient_banks')->insert([
    		[
	            'name'  			=> 'BCA',
	            'account_name' 		=> 'PT Allpay Indonesia',
	            'account_number' 	=> '623182372723',
	            'image_name' 		=> 'bca.png',
	            'status' 			=> 1
            ],
            [
            	'name'  			=> 'Mandiri',
	            'account_name' 		=> 'PT Allpay Indonesia',
	            'account_number' 	=> '273616263612',
	            'image_name' 		=> 'mandiri.png',
	            'status' 			=> 1
            ],
        ]);
    }
}
