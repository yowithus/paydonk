<?php

use Illuminate\Database\Seeder;

use Faker\Factory;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    	DB::table('products')->insert([
    		[
	            'name'     => 'Token Listrik',
	            'code'     => '111',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Tagihan Listrik',
                'code'     => '112',
                'status'   => 1,
                'dji_product_id' => '100301',
            ],
            [
                'name'     => 'PDAM',
                'code'     => '121',
                'status'   => 1,
                'dji_product_id' => '400191',
            ],
        ]);
    }
}
