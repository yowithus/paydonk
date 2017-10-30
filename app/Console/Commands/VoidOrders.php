<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Order;

class VoidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:void';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Void orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $void_orders_count = Order::where('status', 1)
            ->where('updated_at', '<=', Carbon::now()->addMinutes(-10))
            ->update([
                'status' => 0
            ]);

        $this->info(Carbon::now() . ' : ' . $void_orders_count . ' orders have been voided successfully!');
    }
}
