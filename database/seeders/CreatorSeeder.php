<?php

namespace Database\Seeders;

use App\Models\Creator;
use Illuminate\Database\Seeder;

class CreatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Creator::factory()->count(10)->create();
    }
}
