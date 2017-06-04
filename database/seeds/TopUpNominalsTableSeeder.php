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
	            'amount'   => 5000,
	            'status'   => 1
            ],
            [
                'name'     => 'Rp 10.000',
                'amount'   => 10000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 20.000',
                'amount'   => 20000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 25.000',
                'amount'   => 25000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 50.000',
                'amount'   => 50000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 100.000',
                'amount'   => 100000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 250.000',
                'amount'   => 250000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 500.000',
                'amount'   => 500000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 1000.000',
                'amount'   => 1000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 2000.000',
                'amount'   => 2000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 5000.000',
                'amount'   => 5000000,
                'status'   => 1
            ],
            [
                'name'     => 'Rp 10.000.000',
                'amount'   => 10000000,
                'status'   => 1
            ],
        ]);
    }
}
