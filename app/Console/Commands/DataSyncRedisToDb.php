<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DataIngestionController;
use Illuminate\Support\Facades\Log;

class DataSyncRedisToDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:sync-redis-to-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes machine data from Redis to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('DataSyncRedisToDb command started.');

        try {
            $controller = app(DataIngestionController::class);
            $processedRecords = $controller->processRedisStreamData();

            if (empty($processedRecords)) {
                $this->info('No new data found in Redis to synchronize.');
                Log::info('DataSyncRedisToDb: No new data found in Redis.');
            } else {
                $this->info('Data synchronization completed. Processed ' . count($processedRecords) . ' records.');
                Log::info('DataSyncRedisToDb: Data synchronization completed.', ['processed_count' => count($processedRecords)]);
            }
        } catch (\Exception $e) {
            $this->error('Data synchronization failed: ' . $e->getMessage());
            Log::error('DataSyncRedisToDb: Data synchronization failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        Log::info('DataSyncRedisToDb command finished.');
    }
}
