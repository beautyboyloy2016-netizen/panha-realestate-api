<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'House',
                'icon' => 'fa-home',
                'description' => 'Single-family homes and residential houses',
                'sort_order' => 1,
            ],
            [
                'name' => 'Apartment',
                'icon' => 'fa-building',
                'description' => 'Apartments and flats in residential buildings',
                'sort_order' => 2,
            ],
            [
                'name' => 'Villa',
                'icon' => 'fa-landmark',
                'description' => 'Luxury villas and high-end residential properties',
                'sort_order' => 3,
            ],
            [
                'name' => 'Condo',
                'icon' => 'fa-city',
                'description' => 'Condominiums with shared amenities',
                'sort_order' => 4,
            ],
            [
                'name' => 'Townhouse',
                'icon' => 'fa-house-user',
                'description' => 'Multi-story homes sharing walls with neighbors',
                'sort_order' => 5,
            ],
            [
                'name' => 'Land',
                'icon' => 'fa-map',
                'description' => 'Vacant land and plots for development',
                'sort_order' => 6,
            ],
            [
                'name' => 'Commercial',
                'icon' => 'fa-store',
                'description' => 'Commercial properties for business use',
                'sort_order' => 7,
            ],
            [
                'name' => 'Office',
                'icon' => 'fa-briefcase',
                'description' => 'Office spaces and business centers',
                'sort_order' => 8,
            ],
            [
                'name' => 'Warehouse',
                'icon' => 'fa-warehouse',
                'description' => 'Industrial warehouses and storage facilities',
                'sort_order' => 9,
            ],
            [
                'name' => 'Shophouse',
                'icon' => 'fa-store-alt',
                'description' => 'Combined retail and residential properties',
                'sort_order' => 10,
            ],
            [
                'name' => 'Borey',
                'icon' => 'fa-house-chimney',
                'description' => 'Gated community housing developments in Cambodia',
                'sort_order' => 11,
            ],
        ];

        foreach ($types as $type) {
            PropertyType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'slug' => Str::slug($type['name']),
                    'icon' => $type['icon'],
                    'description' => $type['description'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
