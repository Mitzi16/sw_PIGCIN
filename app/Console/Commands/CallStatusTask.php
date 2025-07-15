<?php

namespace App\Console\Commands;

use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CallStatusTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:call-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change of call status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('CallStatusTask started at: ' . Carbon::now());
        $calls = Call::where('status', true)
            ->whereDate('end_date', '<=', Carbon::now())
            ->get();
        foreach ($calls as $call) {
            $call->update(['status' => false]);
        }

        Log::info('CallStatusTask finished at: ' . Carbon::now());
    }
}
