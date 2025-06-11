<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\University;
use App\Models\Department;
use Illuminate\Support\Facades\File;

class UniversitySeeder extends Seeder
{
    public function run()
    {
        $json = File::get(database_path('data/universities.json'));
        $universities = json_decode($json, true);

        foreach ($universities as $uni) {
            $university = University::create([
                'id' => $uni['id'], // optional: if you want to force IDs
                'university_name' => $uni['name'],
                'city_id' => 1, // Adjust this as needed or look it up dynamically
            ]);

            foreach ($uni['programs'] as $program) {
                Department::create([
                    'id' => $program['id'], // optional: if you want to force IDs
                    'department_name' => $program['name'],
                    'university_id' => $university->id,
                ]);
            }
        }
    }
}
