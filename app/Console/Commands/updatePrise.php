<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\track_controller;


class updatePrise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update_prise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to run the program';




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
        $req1 = ["secands" => 0, "last_req" => null];
        $req2 = ["secands" => 0, "last_req" => null];
        $req3 = ["secands" => 0, "last_req" => null];
        while (1) {
            $time = microtime(true);
            // if ($time >= $req1["secands"]) {
            //     $req1 = $this->make_req(["id" => "11329712394179661824", "price" => 113998, "last_req" => $req1["last_req"], "asset" => "BTC", "fiat" => "SAR", "track_type" => "choce_best_price", "trade_way" => "BUY"]);
            // }

            if ($time >= $req2["secands"]) {
                $price_multiplied = 1.007;
                $req2 = $this->make_req(["name" => "BUY BTC track", "price" => 113998, "last_req" => $req2["last_req"], "asset" => "BTC", "fiat" => "USD", "track_type" => "good_dule", "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "BUY", "price_multiplied" => $price_multiplied]);
            }
            if ($time >= $req3["secands"]) {
                $price_multiplied = 1.013;
                $req3 = $this->make_req(["name" => "SELL USDT track", "price" => 113998, "last_req" => $req3["last_req"], "asset" => "USDT", "fiat" => "USD", "track_type" => "good_dule", "payTypes" => "Wise", "price_type" => "auto", "trade_type" => "SELL", "price_multiplied" => $price_multiplied]);
            }
            // if ($time >= $req2["secands"]) {
            //     $req2 = $this->make_req("11464719677996802048", 6998, $req2["last_req"], "ETH", "SAR");
            // }
            sleep(100);
        }
        return Command::SUCCESS;
    }
    public function make_req($data)
    {
        if ($data["track_type"] == "choce_best_price") {
            $ApiController = new ApiController;
            $req = $ApiController->changprics($data);
        }
        if ($data["track_type"] == "good_dule") {
            $trackController = new track_controller;
            $req = $trackController->track_orders($data);
        }

        // 11464719677996802048
        if ($req != $data["last_req"]) {
            // $last_req = $req;
            echo ($data["name"] . ":" . $req);
            echo ("\n");
            // info($req);
        }
        $secands = microtime(true);
        if ($req == "ad is turn off in binance") {
            $secands = $secands + 200;
        }
        if ($req == "ad have best price") {
            $secands = $secands + 75;
        }
        if ($req == "You need to log in") {
            $secands = $secands + 60;
        }
        if ($req == "ad out of amount") {
            $secands = $secands + 120;
        }

        return ["secands" => $secands, "last_req" => $req];
    }
}
