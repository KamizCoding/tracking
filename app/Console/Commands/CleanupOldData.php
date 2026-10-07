<?php

namespace App\Console\Commands;

use App\Models\Click;
use App\Models\PrivacySettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-old-data')]
#[Description('Clean up old click data based on privacy settings')]
class CleanupOldData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data cleanup...');

        $privacySettings = PrivacySettings::all();
        $totalDeleted = 0;

        foreach ($privacySettings as $settings) {
            $cutoffDate = now()->subDays($settings->data_retention_days);

            $deleted = Click::whereHas('trackingLink', function ($query) use ($settings) {
                $query->where('user_id', $settings->user_id);
            })
                ->where('clicked_at', '<', $cutoffDate)
                ->delete();

            $totalDeleted += $deleted;

            $this->info("Deleted {$deleted} old clicks for user {$settings->user_id} (retention: {$settings->data_retention_days} days)");
        }

        $this->info("Cleanup complete. Total records deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }
}
