<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class QuestSeeder extends Seeder
{
    /**
     * Import quests from questSettings XML file into database.
     */
    public function run(): void
    {
        Artisan::call('quest:parse');
        echo Artisan::output();
    }
}
