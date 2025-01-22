<?php

namespace App\Console\Commands;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Matches;
use Illuminate\Support\Str;

class StoreMatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store matches for the next month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = Carbon::now();
        $daysToStore = 30; // Matches for the next month
        
        // Loop through the days and create matches
        for ($id = 1; $id <= $daysToStore; $id++) {
            Matches::updateOrCreate([
                'starting_at' => $startDate->copy()->addDays($id)->toDateString(), // Adjusting the date for each match
            ], [
                'status' => 'upcoming',
                'api_id' => $id,
                'sport_id' => $id,
                'league_id' => $id,
                'name' => Str::uuid() // Adding a dynamic name
            ]);
        }

        $this->info('Matches for the next month have been stored successfully.');
    }
    }

