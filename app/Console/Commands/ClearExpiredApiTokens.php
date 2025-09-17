<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class ClearExpiredApiTokens extends Command
{
    protected $signature = 'tokens:clear-expired';
    protected $description = 'Clear expired API tokens from users table';

    public function handle()
    {
        $count = User::whereNotNull('token_expires_at')
                     ->where('token_expires_at', '<', Carbon::now())
                     ->update(['api_token' => null, 'token_expires_at' => null]);

        $this->info("Cleared {$count} expired tokens.");
        return 0;
    }
}
