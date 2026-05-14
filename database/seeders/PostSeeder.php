<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $categories = PostCategory::all();
        $tags = PostTag::all();

        $posts = [
            [
                'title' => 'Cambodia Real Estate Market Outlook 2025',
                'excerpt' => 'A comprehensive analysis of the Cambodia real estate market trends and predictions for 2025.',
                'content' => '<h2>Market Overview</h2><p>The Cambodia real estate market continues to show strong growth potential heading into 2025. With increased foreign investment and infrastructure development, property values in key areas are expected to rise steadily.</p><h3>Key Trends</h3><ul><li>Increased demand for luxury condominiums in Phnom Penh</li><li>Growing interest in Sihanoukville coastal properties</li><li>Expansion of borey developments in suburban areas</li></ul><p>Investors are particularly interested in mixed-use developments that combine residential and commercial spaces.</p>',
                'status' => 'published',
                'is_featured' => true,
                'category' => 'Market Insights',
                'tags' => ['Investment', 'Phnom Penh', 'Market Trend'],
            ],
            [
                'title' => 'Top 5 Investment Hotspots in Phnom Penh',
                'excerpt' => 'Discover the most promising areas for real estate investment in Cambodia\'s capital city.',
                'content' => '<h2>Investment Opportunities</h2><p>Phnom Penh offers numerous investment opportunities for both local and foreign investors. Here are the top 5 areas to consider:</p><h3>1. BKK1 (Boeung Keng Kang 1)</h3><p>The most prestigious residential area with high rental yields.</p><h3>2. Toul Kork</h3><p>Emerging as a popular choice for families and expats.</p><h3>3. Chroy Changvar</h3><p>Rapidly developing with new infrastructure projects.</p><h3>4. Sen Sok</h3><p>Affordable options with growing amenities.</p><h3>5. Chamkarmon</h3><p>Central location with commercial potential.</p>',
                'status' => 'published',
                'is_featured' => true,
                'category' => 'Investment Tips',
                'tags' => ['Phnom Penh', 'Investment', 'Hot Deal'],
            ],
            [
                'title' => 'Guide to Buying Property in Cambodia as a Foreigner',
                'excerpt' => 'Everything you need to know about property ownership laws and processes for foreign buyers.',
                'content' => '<h2>Foreign Ownership in Cambodia</h2><p>While foreigners cannot directly own land in Cambodia, there are legal structures that allow foreign investment in property.</p><h3>Options for Foreign Buyers</h3><ul><li>Strata title ownership (condos above ground floor)</li><li>Long-term leases (up to 50 years, renewable)</li><li>Company structure ownership</li></ul><h3>Legal Requirements</h3><p>It is essential to work with a reputable lawyer and real estate agent when purchasing property in Cambodia.</p>',
                'status' => 'published',
                'is_featured' => false,
                'category' => 'Property Guides',
                'tags' => ['Guide', 'Tips', 'Investment'],
            ],
            [
                'title' => 'New Luxury Condo Development Launches in BKK1',
                'excerpt' => 'A world-class condominium project has been announced in the heart of Phnom Penh.',
                'content' => '<h2>Project Announcement</h2><p>A new luxury condominium development has been announced in the prestigious BKK1 district. The project features world-class amenities and stunning city views.</p><h3>Project Features</h3><ul><li>Sky pool and fitness center</li><li>24/7 security and concierge</li><li>Underground parking</li><li>Smart home technology</li></ul><p>Pre-sales are expected to begin next month with attractive early bird discounts.</p>',
                'status' => 'published',
                'is_featured' => true,
                'category' => 'Development Updates',
                'tags' => ['Condo', 'Phnom Penh', 'Luxury', 'New Launch'],
            ],
            [
                'title' => 'Sihanoukville Property Market Recovery',
                'excerpt' => 'The coastal city shows signs of recovery with new investment flowing in.',
                'content' => '<h2>Market Recovery</h2><p>After a period of adjustment, Sihanoukville\'s property market is showing strong signs of recovery. New regulations and improved infrastructure are attracting quality investments.</p><h3>Key Developments</h3><ul><li>New international airport expansion</li><li>Improved road connectivity</li><li>Sustainable tourism initiatives</li></ul><p>Beach-front properties and resort developments are particularly in demand.</p>',
                'status' => 'published',
                'is_featured' => false,
                'category' => 'Real Estate News',
                'tags' => ['Sihanoukville', 'Investment', 'Market Trend'],
            ],
            [
                'title' => 'Understanding Property Taxes in Cambodia',
                'excerpt' => 'A complete guide to property taxes and fees for property owners in Cambodia.',
                'content' => '<h2>Property Tax Guide</h2><p>Understanding the tax obligations for property ownership in Cambodia is essential for all investors.</p><h3>Types of Property Taxes</h3><ul><li>Property tax: 0.1% of assessed value annually</li><li>Transfer tax: 4% of sale price</li><li>Rental income tax: Progressive rates</li></ul><h3>Tax Exemptions</h3><p>Properties valued below a certain threshold may be exempt from annual property tax.</p>',
                'status' => 'published',
                'is_featured' => false,
                'category' => 'Legal & Finance',
                'tags' => ['Guide', 'Tips', 'Investment'],
            ],
            [
                'title' => 'Best Boreys for Family Living in 2025',
                'excerpt' => 'Top borey developments offering the best value and amenities for families.',
                'content' => '<h2>Family-Friendly Boreys</h2><p>Borey developments continue to be popular choices for families seeking secure and well-planned communities.</p><h3>Top Picks</h3><ul><li>Borey Peng Huoth - Multiple locations with excellent amenities</li><li>Borey Chip Mong - Modern designs and strong security</li><li>Borey Vimean Phnom Penh - Affordable options</li></ul><p>Consider proximity to schools, hospitals, and workplaces when choosing a borey.</p>',
                'status' => 'published',
                'is_featured' => true,
                'category' => 'Lifestyle',
                'tags' => ['Borey', 'Phnom Penh', 'Affordable', 'Guide'],
            ],
            [
                'title' => 'Draft: Upcoming Real Estate Event',
                'excerpt' => 'Information about an upcoming real estate exhibition and networking event.',
                'content' => '<h2>Event Details Coming Soon</h2><p>Stay tuned for more information about this exciting real estate event.</p>',
                'status' => 'draft',
                'is_featured' => false,
                'category' => 'Real Estate News',
                'tags' => ['Featured'],
            ],
        ];

        foreach ($posts as $postData) {
            $category = $categories->where('name', $postData['category'])->first();
            $postTags = $tags->whereIn('name', $postData['tags'])->pluck('id')->toArray();

            $post = Post::updateOrCreate(
                ['title' => $postData['title']],
                [
                    'user_id' => $user->id,
                    'category_id' => $category?->id,
                    'slug' => Str::slug($postData['title']),
                    'excerpt' => $postData['excerpt'],
                    'content' => $postData['content'],
                    'status' => $postData['status'],
                    'is_featured' => $postData['is_featured'],
                    'published_at' => $postData['status'] === 'published' ? now()->subDays(rand(1, 30)) : null,
                    'views' => rand(50, 5000),
                ]
            );

            $post->tags()->sync($postTags);
        }
    }
}
