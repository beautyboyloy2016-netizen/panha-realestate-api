<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            // Amenities
            [
                'name' => 'Swimming Pool',
                'icon' => 'fa-swimming-pool',
                'category' => 'amenities',
                'description' => 'Private or shared swimming pool',
                'sort_order' => 1,
            ],
            [
                'name' => 'Garden',
                'icon' => 'fa-leaf',
                'category' => 'amenities',
                'description' => 'Private garden or landscaped area',
                'sort_order' => 2,
            ],
            [
                'name' => 'Gym',
                'icon' => 'fa-dumbbell',
                'category' => 'amenities',
                'description' => 'Fitness center or gym facilities',
                'sort_order' => 3,
            ],
            [
                'name' => 'Rooftop',
                'icon' => 'fa-cloud',
                'category' => 'amenities',
                'description' => 'Rooftop terrace or deck',
                'sort_order' => 4,
            ],
            [
                'name' => 'Sauna',
                'icon' => 'fa-hot-tub',
                'category' => 'amenities',
                'description' => 'Sauna or steam room',
                'sort_order' => 5,
            ],
            [
                'name' => 'Jacuzzi',
                'icon' => 'fa-bath',
                'category' => 'amenities',
                'description' => 'Jacuzzi or hot tub',
                'sort_order' => 6,
            ],

            // Facilities
            [
                'name' => 'Parking',
                'icon' => 'fa-parking',
                'category' => 'facilities',
                'description' => 'Covered or open parking space',
                'sort_order' => 10,
            ],
            [
                'name' => 'Security',
                'icon' => 'fa-shield-alt',
                'category' => 'facilities',
                'description' => '24/7 security or guard',
                'sort_order' => 11,
            ],
            [
                'name' => 'Elevator',
                'icon' => 'fa-arrow-up',
                'category' => 'facilities',
                'description' => 'Elevator access',
                'sort_order' => 12,
            ],
            [
                'name' => 'Storage',
                'icon' => 'fa-box',
                'category' => 'facilities',
                'description' => 'Storage room or unit',
                'sort_order' => 13,
            ],
            [
                'name' => 'Laundry',
                'icon' => 'fa-tshirt',
                'category' => 'facilities',
                'description' => 'Laundry room or service',
                'sort_order' => 14,
            ],
            [
                'name' => 'CCTV',
                'icon' => 'fa-video',
                'category' => 'facilities',
                'description' => 'CCTV surveillance system',
                'sort_order' => 15,
            ],

            // Interior Features
            [
                'name' => 'Air Conditioning',
                'icon' => 'fa-snowflake',
                'category' => 'interior',
                'description' => 'Central or split air conditioning',
                'sort_order' => 20,
            ],
            [
                'name' => 'Balcony',
                'icon' => 'fa-door-open',
                'category' => 'interior',
                'description' => 'Private balcony or terrace',
                'sort_order' => 21,
            ],
            [
                'name' => 'Furnished',
                'icon' => 'fa-couch',
                'category' => 'interior',
                'description' => 'Fully or partially furnished',
                'sort_order' => 22,
            ],
            [
                'name' => 'Built-in Wardrobe',
                'icon' => 'fa-door-closed',
                'category' => 'interior',
                'description' => 'Built-in closets or wardrobes',
                'sort_order' => 23,
            ],
            [
                'name' => 'Kitchen Appliances',
                'icon' => 'fa-blender',
                'category' => 'interior',
                'description' => 'Kitchen with appliances',
                'sort_order' => 24,
            ],

            // Outdoor & Lifestyle
            [
                'name' => 'Pet Friendly',
                'icon' => 'fa-paw',
                'category' => 'lifestyle',
                'description' => 'Pets allowed',
                'sort_order' => 30,
            ],
            [
                'name' => 'BBQ Area',
                'icon' => 'fa-fire',
                'category' => 'lifestyle',
                'description' => 'BBQ or outdoor cooking area',
                'sort_order' => 31,
            ],
            [
                'name' => 'Playground',
                'icon' => 'fa-child',
                'category' => 'lifestyle',
                'description' => 'Children playground',
                'sort_order' => 32,
            ],

            // Utilities
            [
                'name' => 'Internet',
                'icon' => 'fa-wifi',
                'category' => 'utilities',
                'description' => 'High-speed internet connection',
                'sort_order' => 40,
            ],
            [
                'name' => 'Cable TV',
                'icon' => 'fa-tv',
                'category' => 'utilities',
                'description' => 'Cable or satellite TV',
                'sort_order' => 41,
            ],
            [
                'name' => 'Water Heater',
                'icon' => 'fa-fire-alt',
                'category' => 'utilities',
                'description' => 'Hot water heater',
                'sort_order' => 42,
            ],
            [
                'name' => 'Backup Generator',
                'icon' => 'fa-bolt',
                'category' => 'utilities',
                'description' => 'Backup power generator',
                'sort_order' => 43,
            ],
            [
                'name' => 'Solar Panels',
                'icon' => 'fa-solar-panel',
                'category' => 'utilities',
                'description' => 'Solar energy panels',
                'sort_order' => 44,
            ],
        ];

        foreach ($features as $feature) {
            Feature::updateOrCreate(
                ['name' => $feature['name']],
                [
                    'slug' => Str::slug($feature['name']),
                    'icon' => $feature['icon'],
                    'category' => $feature['category'],
                    'description' => $feature['description'],
                    'sort_order' => $feature['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
