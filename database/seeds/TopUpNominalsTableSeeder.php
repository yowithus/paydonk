<?php

use Illuminate\Database\Seeder;

use Faker\Factory;

class TopUpNominalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	DB::table('topup_nominals')->insert([
    		[
	            'name'     => 'Rp 5.000',
	            'price'    => 5000,
	            'status'   => 1
            ],
            [
                'name'     => 'Rp 10.000',
                'price'    => 10000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 20.000',
                'price'    => 20000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 25.000',
                'price'    => 25000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 50.000',
                'price'    => 50000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 100.000',
                'price'    => 100000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 250.000',
                'price'    => 250000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 500.000',
                'price'    => 500000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 1000.000',
                'price'    => 1000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 2000.000',
                'price'    => 2000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 5000.000',
                'price'    => 5000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 10.000.000',
                'price'    => 10000000,
                'status'   => 1
            ],
        ]);
    }
}
