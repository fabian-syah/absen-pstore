<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncUserRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:sync-ranks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize User XP and Ranks based on 4 months of attendance history';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = \App\Models\User::all();
        $this->info('Starting Rank Synchronization for ' . $users->count() . ' users...');
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->syncXpFromHistory();
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSynchronization completed successfully!");

        return Command::SUCCESS;
    }
}
