<?php

use Illuminate\Database\Seeder;

use Faker\Factory;

class PromosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('promos')->insert([
            [
                'name'         => 'Allpay Launching Promo',
                'code'         => 'ALLPAY',
                'discount_percentage'   => 30,
                'max_discount'  => 100000,
                'min_usage'     => 30000,
                'started_at'    => '2017-09-01 12:00:00',
                'ended_at'      => '2017-12-31 12:00:00',
                'status'        => 1
            ],
    		[
	            'name'         => 'PayDonk',
	            'code'         => 'PAYDONK',
	            'discount_percentage'   => 30,
                'max_discount'  => 100000,
                'min_usage'     => 30000,
                'started_at'    => '2017-09-01 12:00:00',
                'ended_at'      => '2017-12-31 12:00:00',
                'status'        => 1
            ],
        ]);
    }
}
