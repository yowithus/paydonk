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
            // Token Listrik
    		[
	            'name'     => 'Token Listrik Rp 20.000',
                'category' => 'Token Listrik',
                'price'     => 20000,
                'province' => null,
                'region'   => null,
	            'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Token Listrik Rp 50.000',
                'category' => 'Token Listrik',
                'price'     => 50000,
                'province' => null,
                'region'   => null,
                'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Token Listrik Rp 100.000',
                'category' => 'Token Listrik',
                'price'     => 100000,
                'province' => null,
                'region'   => null,
                'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Token Listrik Rp 200.000',
                'category' => 'Token Listrik',
                'price'     => 200000,
                'province' => null,
                'region'   => null,
                'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Token Listrik Rp 500.000',
                'category' => 'Token Listrik',
                'price'     => 500000,
                'province' => null,
                'region'   => null,
                'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],
            [
                'name'     => 'Token Listrik Rp 1.000.000',
                'category' => 'Token Listrik',
                'price'     => 1000000,
                'province' => null,
                'region'   => null,
                'code'     => '1101',
                'status'   => 1,
                'dji_product_id' => '100302',
            ],

            // Tagihan Listrik
            [
                'name'     => 'Tagihan Listrik',
                'category' => 'Tagihan Listrik',
                'price'     => null,
                'province' => null,
                'region'   => null,
                'code'     => '1102',
                'status'   => 1,
                'dji_product_id' => '100301',
            ],

            // PDAM
            [
                'name'     => 'PDAM Kab Banyumas',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB BANYUMAS',
                'code'     => '1201',
                'status'   => 1,
                'dji_product_id' => '400011',
            ],
            [
                'name'     => 'PDAM Kab Kebumen',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB KEBUMEN',
                'code'     => '1202',
                'status'   => 1,
                'dji_product_id' => '400021',
            ],
            [
                'name'     => 'PDAM Kota Magelang',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KOTA MAGELANG',
                'code'     => '1203',
                'status'   => 1,
                'dji_product_id' => '400041',
            ],
            [
                'name'     => 'PDAM Kab Cilacap',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB CILACAP',
                'code'     => '1204',
                'status'   => 1,
                'dji_product_id' => '400051',
            ],
            [
                'name'     => 'PDAM Kota Semarang',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KOTA SEMARANG',
                'code'     => '1205',
                'status'   => 1,
                'dji_product_id' => '400061',
            ],
            [
                'name'     => 'PDAM Kab Sleman',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB SLEMAN',
                'code'     => '1206',
                'status'   => 1,
                'dji_product_id' => '400071',
            ],
            [
                'name'     => 'PDAM Kab BOYOLALI',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB BOYOLALI',
                'code'     => '1207',
                'status'   => 1,
                'dji_product_id' => '400081',
            ],
            [
                'name'     => 'PDAM Kab Jepara',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB JEPARA',
                'code'     => '1208',
                'status'   => 1,
                'dji_product_id' => '400091',
            ],
            [
                'name'     => 'PDAM Kab Pekalongan',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB PEKALONGAN',
                'code'     => '1209',
                'status'   => 1,
                'dji_product_id' => '400101',
            ],
            [
                'name'     => 'PDAM Kota Banjar',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KOTA BANJAR',
                'code'     => '1210',
                'status'   => 1,
                'dji_product_id' => '400111',
            ],
            [
                'name'     => 'PDAM Kab Karanganyar',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB KARANGANYAR',
                'code'     => '1211',
                'status'   => 1,
                'dji_product_id' => '400121',
            ],
            [
                'name'     => 'PDAM Kab Wonogiri',
                'category' => 'PDAM',
                'price'     => null,
                'province' => 'Jawa Tengah',
                'region'   => 'KAB WONOGIRI',
                'code'     => '1212',
                'status'   => 1,
                'dji_product_id' => '400141',
            ],
        ]);
    }
}
