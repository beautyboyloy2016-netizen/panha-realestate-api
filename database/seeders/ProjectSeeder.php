<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            // Featured New Developments
            [
                'name' => 'Picasso Sky Gemme',
                'location' => 'BKK1, Phnom Penh',
                'developer' => 'Global Titan Stone',
                'units' => 520,
                'price_from' => '320,000',
                'completion' => 'Q4 2028',
                'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
                'featured' => true,
                'description' => '52-story luxury masterpiece in BKK1 featuring all-around wellness facilities and premium retail spaces.',
            ],
            [
                'name' => 'J-Tower 3',
                'location' => 'Chamkarmon, Phnom Penh',
                'developer' => 'J-Tower Development',
                'units' => 380,
                'price_from' => '185,000',
                'completion' => 'Q2 2026',
                'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
                'featured' => true,
                'description' => 'Rising above the rest in central Phnom Penh with cutting-edge design and world-class amenities.',
            ],
            [
                'name' => 'The Bridge Twin Towers',
                'location' => 'BKK1, Phnom Penh',
                'developer' => 'Singapore Consortium',
                'units' => 450,
                'price_from' => '280,000',
                'completion' => 'Q3 2026',
                'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
                'featured' => true,
                'description' => '$300 million twin-tower development featuring commercial and residential spaces in downtown.',
            ],
            [
                'name' => 'Le Condé BKK1',
                'location' => 'BKK1, Phnom Penh',
                'developer' => 'Condé Group',
                'units' => 240,
                'price_from' => '220,000',
                'completion' => 'Q1 2026',
                'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
                'featured' => true,
                'description' => 'Smart home luxury condominiums with fully furnished units and sophisticated control systems.',
            ],
            [
                'name' => 'Marina Bay Residence',
                'location' => 'Chroy Changvar, Phnom Penh',
                'developer' => 'Borey Peng Huoth',
                'units' => 420,
                'price_from' => '195,000',
                'completion' => 'Q4 2025',
                'image_url' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
                'featured' => true,
                'description' => 'Waterfront luxury development with panoramic Mekong River views and resort-style living.',
            ],
            [
                'name' => 'Diamond Heights',
                'location' => 'Diamond Island, Phnom Penh',
                'developer' => 'Oxley Cambodia',
                'units' => 680,
                'price_from' => '165,000',
                'completion' => 'Q2 2027',
                'image_url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
                'featured' => true,
                'description' => 'Iconic island development with exclusive sky facilities and 360-degree city views.',
            ],
            [
                'name' => 'Skyline Metropolitan',
                'location' => 'Veal Vong, Phnom Penh',
                'developer' => 'Urban Living Group',
                'units' => 550,
                'price_from' => '145,000',
                'completion' => 'Q3 2026',
                'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
                'featured' => true,
                'description' => 'Modern mixed-use development combining residential luxury with commercial convenience.',
            ],
            [
                'name' => 'The Grand Meridian',
                'location' => 'BKK3, Phnom Penh',
                'developer' => 'Meridian Properties',
                'units' => 320,
                'price_from' => '175,000',
                'completion' => 'Q1 2027',
                'image_url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800',
                'featured' => true,
                'description' => 'Sophisticated urban living with premium retail podium and infinity sky pool.',
            ],
            [
                'name' => 'Royal Pearl Towers',
                'location' => 'Daun Penh, Phnom Penh',
                'developer' => 'Pearl Development',
                'units' => 480,
                'price_from' => '210,000',
                'completion' => 'Q4 2026',
                'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
                'featured' => true,
                'description' => 'Twin towers offering luxury residences with royal heritage design and modern amenities.',
            ],
            [
                'name' => 'Eco Park Residence',
                'location' => 'Toul Kork, Phnom Penh',
                'developer' => 'Green City Cambodia',
                'units' => 360,
                'price_from' => '135,000',
                'completion' => 'Q2 2026',
                'image_url' => 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=800',
                'featured' => true,
                'description' => 'Sustainable living complex with vertical gardens, solar power, and green certification.',
            ],
            [
                'name' => 'Platinum Suites',
                'location' => 'BKK2, Phnom Penh',
                'developer' => 'Platinum Group',
                'units' => 280,
                'price_from' => '190,000',
                'completion' => 'Q3 2025',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
                'featured' => true,
                'description' => 'Boutique luxury development with hotel-style services and exclusive member facilities.',
            ],
            [
                'name' => 'Sunrise Garden City',
                'location' => 'Sen Sok, Phnom Penh',
                'developer' => 'Century Properties',
                'units' => 750,
                'price_from' => '95,000',
                'completion' => 'Q1 2027',
                'image_url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800',
                'featured' => true,
                'description' => 'Large-scale integrated township with schools, hospitals, and commercial centers.',
            ],

            // High Rental Yield Properties
            [
                'name' => 'Central Business Tower',
                'location' => 'BKK1, Phnom Penh',
                'developer' => 'CBD Developers',
                'units' => 220,
                'price_from' => '160,000',
                'completion' => 'Q3 2025',
                'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
                'featured' => false,
                'rental_yield' => 9.5,
                'description' => 'Prime CBD location with guaranteed rental program and professional management services.',
            ],
            [
                'name' => 'University Quarter',
                'location' => 'Russian Market, Phnom Penh',
                'developer' => 'Education Properties',
                'units' => 180,
                'price_from' => '85,000',
                'completion' => 'Q4 2025',
                'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
                'featured' => false,
                'rental_yield' => 10.2,
                'description' => 'Student-focused apartments near major universities with high rental demand year-round.',
            ],
            [
                'name' => 'Metro Station Residences',
                'location' => 'Toul Kork, Phnom Penh',
                'developer' => 'Transit Development',
                'units' => 320,
                'price_from' => '125,000',
                'completion' => 'Q2 2026',
                'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
                'featured' => false,
                'rental_yield' => 8.8,
                'description' => 'Transit-oriented development near future metro station with excellent rental prospects.',
            ],
            [
                'name' => 'Airport Business Park',
                'location' => 'Chbar Ampov, Phnom Penh',
                'developer' => 'Gateway Developers',
                'units' => 260,
                'price_from' => '105,000',
                'completion' => 'Q1 2026',
                'image_url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800',
                'featured' => false,
                'rental_yield' => 9.0,
                'description' => 'Strategic location near airport with corporate housing demand and serviced apartment options.',
            ],
            [
                'name' => 'Tech Park Apartments',
                'location' => 'Chamkarmon, Phnom Penh',
                'developer' => 'Innovation Properties',
                'units' => 200,
                'price_from' => '140,000',
                'completion' => 'Q3 2025',
                'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
                'featured' => false,
                'rental_yield' => 8.5,
                'description' => 'Adjacent to technology business park with built-in tenant base from tech companies.',
            ],
            [
                'name' => 'Riverside Studios',
                'location' => 'Daun Penh, Phnom Penh',
                'developer' => 'River View Group',
                'units' => 150,
                'price_from' => '95,000',
                'completion' => 'Q4 2025',
                'image_url' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
                'featured' => false,
                'rental_yield' => 9.8,
                'description' => 'Compact studio units ideal for young professionals with riverside lifestyle amenities.',
            ],
            [
                'name' => 'Embassy Gardens',
                'location' => 'BKK3, Phnom Penh',
                'developer' => 'Diplomatic Quarter Dev',
                'units' => 140,
                'price_from' => '175,000',
                'completion' => 'Q2 2025',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
                'featured' => false,
                'rental_yield' => 8.2,
                'description' => 'Premium apartments in embassy district with diplomatic and expat tenant demand.',
            ],
            [
                'name' => 'Medical District Tower',
                'location' => 'Olympic, Phnom Penh',
                'developer' => 'Healthcare Properties',
                'units' => 180,
                'price_from' => '115,000',
                'completion' => 'Q1 2026',
                'image_url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800',
                'featured' => false,
                'rental_yield' => 9.3,
                'description' => 'Strategic location near major hospitals with medical tourism rental opportunities.',
            ],
            [
                'name' => 'Aeon Mall City',
                'location' => 'Sen Sok, Phnom Penh',
                'developer' => 'Retail Properties Group',
                'units' => 240,
                'price_from' => '90,000',
                'completion' => 'Q3 2026',
                'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
                'featured' => false,
                'rental_yield' => 8.7,
                'description' => 'Connected to major shopping mall with guaranteed foot traffic and rental demand.',
            ],
        ];

        foreach ($projects as $projectData) {
            // Create the project with default English values
            $project = Project::create($projectData);

            // Add translations for all languages
            $locales = ['en', 'km', 'zh', 'fr'];

            // Get translatable fields that exist in the project data
            $translatableFields = ['name', 'location', 'developer', 'description', 'price_from', 'completion'];

            // Translation mappings for each project field
            $translations = [
                'en' => [
                    'name' => $projectData['name'] ?? '',
                    'location' => $projectData['location'] ?? '',
                    'developer' => $projectData['developer'] ?? '',
                    'description' => $projectData['description'] ?? '',
                    'price_from' => $projectData['price_from'] ?? '',
                    'completion' => $projectData['completion'] ?? '',
                ],
                'km' => [
                    'name' => ($projectData['name'] ?? '') . ' (ខ្មែរ)',
                    'location' => $projectData['location'] ?? '',
                    'developer' => $projectData['developer'] ?? '',
                    'description' => ($projectData['description'] ?? '') . ' [ការពិពណ៌នាជាភាសាខ្មែរ]',
                    'price_from' => $projectData['price_from'] ?? '',
                    'completion' => $projectData['completion'] ?? '',
                ],
                'zh' => [
                    'name' => ($projectData['name'] ?? '') . ' (中文)',
                    'location' => $projectData['location'] ?? '',
                    'developer' => $projectData['developer'] ?? '',
                    'description' => ($projectData['description'] ?? '') . ' [中文描述]',
                    'price_from' => $projectData['price_from'] ?? '',
                    'completion' => $projectData['completion'] ?? '',
                ],
                'fr' => [
                    'name' => ($projectData['name'] ?? '') . ' (Français)',
                    'location' => $projectData['location'] ?? '',
                    'developer' => $projectData['developer'] ?? '',
                    'description' => ($projectData['description'] ?? '') . ' [Description en français]',
                    'price_from' => $projectData['price_from'] ?? '',
                    'completion' => $projectData['completion'] ?? '',
                ],
            ];

            // Set translations for each locale
            foreach ($locales as $locale) {
                if (isset($translations[$locale])) {
                    foreach ($translations[$locale] as $field => $value) {
                        if (!empty($value) && in_array($field, $translatableFields)) {
                            $project->setTranslation($field, $value, $locale);
                        }
                    }
                }
            }
        }

        $this->command->info('Projects seeded successfully with translations!');
    }
}
