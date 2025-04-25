<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Poll;

class DeactivateExpiredPolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deactivate-expired-polls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark polls as EXPIRED when their expiration date passes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredPolls = Poll::where('expires_at', '<=', now())
            ->active()
            ->update(['status' => 'EXPIRED']);

        $this->info("Updated $expiredPolls poll(s) to EXPIRED.");
    }
}
