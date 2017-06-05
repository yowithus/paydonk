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
	            'account_number' 	=> '6231823727',
	            'image_name' 		=> 'bca.png',
	            'status' 			=> 1
            ],
            [
            	'name'  			=> 'Mandiri',
	            'account_name' 		=> 'PT Allpay Indonesia',
	            'account_number' 	=> '2736162636',
	            'image_name' 		=> 'mandiri.png',
	            'status' 			=> 1
            ],
            [
                'name'              => 'BNI',
                'account_name'      => 'PT Allpay Indonesia',
                'account_number'    => '3517263617',
                'image_name'        => 'bni.png',
                'status'            => 1
            ],
            [
                'name'              => 'BRI',
                'account_name'      => 'PT Allpay Indonesia',
                'account_number'    => '9172636172',
                'image_name'        => 'bri.png',
                'status'            => 1
            ],
            [
                'name'              => 'Permata',
                'account_name'      => 'PT Allpay Indonesia',
                'account_number'    => '5362617263',
                'image_name'        => 'permata.png',
                'status'            => 1
            ],
            [
                'name'              => 'CIMB',
                'account_name'      => 'PT Allpay Indonesia',
                'account_number'    => '7612663616',
                'image_name'        => 'cimb.png',
                'status'            => 1
            ],
        ]);
    }
}
