<?php

use Illuminate\Database\Seeder;

use Faker\Factory;
use App\SenderBank;

class SenderBanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	SenderBank::insert([
    		[
	            'name'  	   => 'BCA',
	            'image_name'   => 'bca.png',
	            'status'       => 1
            ],
            [
            	'name'         => 'Mandiri',
	            'image_name'   => 'mandiri.png',
	            'status' 	   => 1
            ],
            [
                'name'         => 'BNI',
                'image_name'   => 'bni.png',
                'status'       => 1
            ],
            [
                'name'         => 'BRI',
                'image_name'   => 'bri.png',
                'status'       => 1
            ],
            [
                'name'         => 'Permata',
                'image_name'   => 'permata.png',
                'status'       => 1
            ],
            [
                'name'         => 'CIMB',
                'image_name'   => 'cimb.png',
                'status'       => 1
            ],
        ]);
    }
}
