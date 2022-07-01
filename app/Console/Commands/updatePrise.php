<?php

namespace App\Console\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Http\Controllers\ApiController;

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
    public function handle(Schedule $schedule)
    {
        $ApiController = new ApiController;

        $req = $ApiController->changprics();
        echo $req;
        if ($req == "program stop") {
            # code...
        }


        // for ($i = 0; $i < 100; $i++) {
        //     echo "test";
        // }
        return Command::SUCCESS;
    }
}