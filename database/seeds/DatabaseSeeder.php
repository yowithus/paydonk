<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->call(RecipientBanksTableSeeder::class);
        $this->call(SenderBanksTableSeeder::class);
        $this->call(TopUpNominalsTableSeeder::class);
        $this->call(PromosTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
    }
}
