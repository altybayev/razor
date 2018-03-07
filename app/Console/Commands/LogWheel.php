<?php

namespace App\Console\Commands;

use App\WheelLogger;
use Illuminate\Console\Command;

class LogWheel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:wheel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log each minute wheel game results';

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
        $results = WheelLogger::logEveryMinute();

        if ($results) $this->info("Wheel game logged successfully! " . join(', ', $results));
        else $this->info("...");
    }
}
