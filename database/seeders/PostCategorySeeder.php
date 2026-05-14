<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Real Estate News',
                'description' => 'Latest news and updates from the real estate market',
                'icon' => 'fa-newspaper',
                'color' => '#3498db',
                'sort_order' => 1,
            ],
            [
                'name' => 'Market Insights',
                'description' => 'In-depth analysis and market trends',
                'icon' => 'fa-chart-line',
                'color' => '#2ecc71',
                'sort_order' => 2,
            ],
            [
                'name' => 'Investment Tips',
                'description' => 'Expert advice on real estate investment',
                'icon' => 'fa-lightbulb',
                'color' => '#f39c12',
                'sort_order' => 3,
            ],
            [
                'name' => 'Property Guides',
                'description' => 'Comprehensive guides for buyers and sellers',
                'icon' => 'fa-book',
                'color' => '#9b59b6',
                'sort_order' => 4,
            ],
            [
                'name' => 'Cambodia Focus',
                'description' => 'News and insights specific to Cambodia real estate',
                'icon' => 'fa-map-marker-alt',
                'color' => '#e74c3c',
                'sort_order' => 5,
            ],
            [
                'name' => 'Development Updates',
                'description' => 'Updates on new developments and projects',
                'icon' => 'fa-building',
                'color' => '#1abc9c',
                'sort_order' => 6,
            ],
            [
                'name' => 'Lifestyle',
                'description' => 'Living and lifestyle content',
                'icon' => 'fa-home',
                'color' => '#e67e22',
                'sort_order' => 7,
            ],
            [
                'name' => 'Legal & Finance',
                'description' => 'Legal and financial advice for property transactions',
                'icon' => 'fa-gavel',
                'color' => '#34495e',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
