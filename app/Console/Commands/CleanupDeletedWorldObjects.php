<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupDeletedWorldObjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:cleanup-deleted {--days=7 : Number of days after soft delete to hard delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hard delete world_objects that have been soft-deleted (deleted=1) for more than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $worlds = DB::table('userworlds')->select('id', 'objects')->get();
        $deletedCount = 0;

        foreach ($worlds as $world) {
            // Hard delete objects where deleted=1 and updated_at is older than cutoff
            $all = unserialize($world->objects);
            if ($all) {
                $kept = array_filter($all, function ($object) use ($cutoffDate) {
                    return !($object->deleted && (Carbon::createFromTimestampMs($object->plantTime)->lt($cutoffDate)));
                });
                
                DB::table('userworlds')->where('id', $world->id)->update(['objects' => serialize($kept)]);
                $deletedCount += count($all) - count($kept);
            }
        }

        $this->info("Hard deleted {$deletedCount} world objects that were soft-deleted more than {$days} days ago.");

        return 0;
    }
}
