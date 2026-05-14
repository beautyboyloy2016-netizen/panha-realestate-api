<?php

namespace Database\Seeders;

use App\Models\PostTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Phnom Penh', 'color' => '#3498db'],
            ['name' => 'Siem Reap', 'color' => '#e74c3c'],
            ['name' => 'Sihanoukville', 'color' => '#2ecc71'],
            ['name' => 'Apartment', 'color' => '#9b59b6'],
            ['name' => 'Villa', 'color' => '#f39c12'],
            ['name' => 'Condo', 'color' => '#1abc9c'],
            ['name' => 'Land', 'color' => '#34495e'],
            ['name' => 'Commercial', 'color' => '#e67e22'],
            ['name' => 'Investment', 'color' => '#27ae60'],
            ['name' => 'Rental', 'color' => '#8e44ad'],
            ['name' => 'Sale', 'color' => '#c0392b'],
            ['name' => 'Luxury', 'color' => '#d35400'],
            ['name' => 'Affordable', 'color' => '#16a085'],
            ['name' => 'New Launch', 'color' => '#2980b9'],
            ['name' => 'Hot Deal', 'color' => '#c0392b'],
            ['name' => 'Borey', 'color' => '#7f8c8d'],
            ['name' => 'Market Trend', 'color' => '#95a5a6'],
            ['name' => 'Tips', 'color' => '#bdc3c7'],
            ['name' => 'Guide', 'color' => '#ecf0f1'],
            ['name' => 'Featured', 'color' => '#f1c40f'],
        ];

        foreach ($tags as $tag) {
            PostTag::updateOrCreate(
                ['name' => $tag['name']],
                [
                    'slug' => Str::slug($tag['name']),
                    'color' => $tag['color'],
                    'is_active' => true,
                ]
            );
        }
    }
}
