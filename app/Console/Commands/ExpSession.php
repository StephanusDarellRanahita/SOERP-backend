<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class ExpSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exp-session';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete session expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('sessions')
            ->where('created_at', '<', now()->subMinutes(config('session.lifetime'))->timestamp)
            ->delete();

        $this->info('Expired sessions deleted successfully.');
    }
}
