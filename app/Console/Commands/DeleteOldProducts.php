<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Console\Command;

class DeleteOldProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete 30 days old products';

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
     * @return int
     */
    public function handle()
    {
        Product::whereDate('created_at', '<', Carbon::now()->subDays(30))->delete();
    }
}
