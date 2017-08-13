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
                'province' => null,
                'region'   => null,
	            'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Tagihan Listrik',
                'province' => null,
                'region'   => null,
                'code'     => '1102',
                'status'   => 1,
                'dji_product_id' => '100301',
            ],
            [
                'name'     => 'PDAM',
                'province' => 'Jawa Tengah',
                'region'   => 'KAB BANYUMAS',
                'code'     => '1201',
                'status'   => 1,
                'dji_product_id' => '400011',
            ],
            [
                'name'     => 'PDAM',
                'province' => 'Jawa Tengah',
                'region'   => 'KAB KEBUMEN',
                'code'     => '1202',
                'status'   => 1,
                'dji_product_id' => '400021',
            ],
            [
                'name'     => 'PDAM',
                'province' => 'Jawa Tengah',
                'region'   => 'KOTA MAGELANG',
                'code'     => '1203',
                'status'   => 1,
                'dji_product_id' => '400041',
            ],
            [
                'name'     => 'PDAM',
                'province' => 'Jawa Tengah',
                'region'   => 'KAB CILACAP',
                'code'     => '1204',
                'status'   => 1,
                'dji_product_id' => '400051',
            ],
        ]);
    }
}
