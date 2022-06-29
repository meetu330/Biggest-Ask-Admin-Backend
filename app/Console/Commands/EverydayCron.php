<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OTP;
use DB;

class EverydayCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'everyday:sendnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will send push notification';

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
        $otp = new OTP;
        $otp->otp = 1234;
        $otp->email = 'test@gmail.com';
        $otp->save();

        // DB::table('o_t_p_s')->delete();
    }
}
