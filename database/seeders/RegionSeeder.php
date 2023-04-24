<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $json = File::get(public_path('data/regions.json'));
        $data = json_decode($json);

        foreach ($data as $item) {
            DB::table('regions')->insert([
                'id' => $item->id,
                'city' => $item->city,
                'region' => $item->region,
                'federal_area' => $item->federal_area ?? null,
                'lat' => $item->lat,
                'lng' => $item->lng,
            ]);
        }
    }
}
