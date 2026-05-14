<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $articles = [
            [
                'title' => 'Phnom Penh Property Market Shows Strong Growth in 2025',
                'category' => 'Market Update',
                'excerpt' => 'The capital city continues to attract both local and foreign investors with new developments and strong rental yields. Market analysis shows sustained growth across all property types.',
                'content' => 'The Cambodian real estate market has demonstrated remarkable resilience and growth in 2025, with Phnom Penh leading the charge. According to recent data from the Ministry of Land Management, property transactions have increased by 15% year-over-year, driven by both domestic and international demand.

Key factors contributing to this growth include improved infrastructure, political stability, and attractive investment opportunities. The condominium market has been particularly robust, with average prices in prime locations such as BKK1 and Chamkarmon increasing by 8-10%.

Foreign investors, particularly from China, Singapore, and South Korea, continue to show strong interest in Cambodia\'s property market. The recent introduction of new infrastructure projects, including the expressway to Sihanoukville and improvements to Phnom Penh\'s public transport system, have further enhanced the market\'s attractiveness.

Real estate experts predict that this upward trend will continue throughout 2025 and beyond, making now an opportune time for both investment and homeownership.',
                'image_url' => 'https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'New Borey Developments Announced in Sen Sok District',
                'category' => 'Development',
                'excerpt' => 'Three major developers have announced plans for new gated communities targeting middle-income families. The projects are expected to deliver over 2,000 units by 2026.',
                'content' => 'Sen Sok district is set to become one of the fastest-growing residential areas in Phnom Penh with the announcement of three major borey developments by leading property developers.

Global Titan Stone, Borey Peng Huoth, and Century Properties have collectively committed to investing over $500 million in these new projects, which will feature modern amenities, green spaces, and family-oriented facilities.

The developments are strategically located near key infrastructure including schools, hospitals, and the upcoming Ring Road 3, making them highly attractive to young families and first-time homebuyers.

"These projects address the growing demand for quality, affordable housing in Phnom Penh," said Mr. Sok Pheng, CEO of Century Properties. "We\'re seeing strong pre-launch interest from both local and overseas Cambodian buyers."

The boreys will feature various house types ranging from townhouses to semi-detached villas, with prices starting from $95,000, making homeownership more accessible to the growing middle class.',
                'image_url' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(13),
            ],
            [
                'title' => 'Condominium Prices Stabilize After Years of Growth',
                'category' => 'Market Analysis',
                'excerpt' => 'Industry experts report a cooling period as the market reaches maturity. Average prices remain steady while transaction volumes increase, indicating healthy market conditions.',
                'content' => 'After several years of rapid price appreciation, Cambodia\'s condominium market is showing signs of maturation and stabilization. According to the latest report by CBRE Cambodia, average condominium prices across Phnom Penh have remained relatively flat over the past six months, marking a shift from the double-digit annual growth seen in previous years.

This stabilization is viewed positively by industry experts, who suggest it indicates a healthy market correction. "A sustainable market needs periods of consolidation," explains Ms. Linda Chen, Head of Research at CBRE Cambodia. "What we\'re seeing now is increased transaction activity at stable prices, which is actually more beneficial for long-term market health than continuous rapid appreciation."

The data shows interesting variations across different areas. Prime locations like BKK1 and Diamond Island continue to see slight appreciation of 3-5% annually, while newer development areas are experiencing price stabilization as supply catches up with demand.

Rental yields remain attractive, averaging 6-8% in prime locations, which continues to draw investor interest despite the slower capital appreciation. The market is also seeing a shift towards quality over quantity, with buyers increasingly favoring well-managed properties with strong facilities and services.',
                'image_url' => 'https://images.unsplash.com/photo-1460472178825-e5240623afd5?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Foreign Investment in Commercial Real Estate Increases',
                'category' => 'Investment',
                'excerpt' => 'International investors show renewed interest in Cambodia commercial properties, particularly in retail and office sectors. Transaction values reach five-year high.',
                'content' => 'Cambodia\'s commercial real estate sector is experiencing a surge in foreign investment, with international investors increasingly recognizing the country\'s potential as a regional business hub.

According to data from the Ministry of Economy and Finance, foreign direct investment in commercial real estate reached $1.2 billion in the first half of 2025, representing a 40% increase compared to the same period last year.

The office sector has been particularly attractive, with Grade A office space in Phnom Penh\'s CBD commanding rents of $25-30 per square meter, with occupancy rates exceeding 85%. Major multinational corporations from technology, financial services, and manufacturing sectors are expanding their presence in Cambodia.

Retail is another hot sector, driven by rising consumer spending power and the growth of Cambodia\'s middle class. International retail brands are increasingly entering the market, seeking prime locations in major shopping centers and high-street locations.

"We\'re seeing sophisticated investors who understand the Cambodia story and are taking long-term positions," says Mr. David Wong, Managing Director of JLL Cambodia. "The commercial sector offers attractive yields of 7-9%, combined with good prospects for capital appreciation as the economy continues to grow."

The government\'s ongoing efforts to improve business regulations and infrastructure are further enhancing investor confidence in Cambodia\'s commercial property market.',
                'image_url' => 'https://images.unsplash.com/photo-1554469384-e58fac16e23a?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(28),
            ],
            [
                'title' => 'New Property Tax Regulations to Take Effect in 2026',
                'category' => 'Legal & Policy',
                'excerpt' => 'Government announces comprehensive property tax framework aimed at improving revenue collection and market transparency. Industry stakeholders express support for clearer regulations.',
                'content' => 'The Cambodian government has announced a new comprehensive property tax framework that will take effect from January 2026, marking a significant step in the country\'s real estate market maturation.

The new regulations will introduce a progressive tax structure based on property values, with residential properties valued under $80,000 remaining exempt. Properties above this threshold will be taxed at rates ranging from 0.1% to 0.5% of assessed value annually.

Minister of Economy and Finance, H.E. Aun Pornmoniroth, stated, "This framework will enhance transparency in the property market and provide sustainable revenue for local development. We have designed it carefully to protect ordinary homeowners while ensuring fair contribution from luxury property owners."

Industry associations have generally welcomed the move, noting that clear tax regulations could actually benefit the market by improving transparency and encouraging more formal transactions. "While nobody likes paying taxes, clear rules are better than uncertainty," commented Mr. John Smith, President of the Cambodia Valuers and Estate Agents Association.

The government has committed to using property tax revenues for local infrastructure improvements, including roads, drainage, and public facilities in areas where the taxes are collected. A grace period will be provided for property owners to register their properties and understand the new requirements before enforcement begins.',
                'image_url' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Sustainable Building Practices Gain Traction in Cambodia',
                'category' => 'Development',
                'excerpt' => 'Developers increasingly adopt green building standards as environmental awareness grows among buyers. First LEED-certified residential project launches in Phnom Penh.',
                'content' => 'Environmental sustainability is becoming a key consideration in Cambodia\'s real estate sector, with developers increasingly incorporating green building practices into their projects.

The trend was highlighted by the launch of Green Valley Estates in Toul Kork, which has become the first residential development in Cambodia to achieve LEED (Leadership in Energy and Environmental Design) certification.

The project features solar panels, rainwater harvesting systems, natural ventilation design, and extensive green spaces. "Sustainability is not just good for the environment, it\'s also good business," explains the project developer. "We\'re seeing strong demand from buyers who value lower utility costs and healthier living environments."

A recent survey by CBRE Cambodia found that 65% of property buyers would pay a premium of 5-10% for properties with verified green features. This growing awareness is driving developers to rethink their approach.

The government has also shown support for sustainable development, introducing incentives for buildings that meet recognized environmental standards, including faster permit approvals and reduced utility connection fees.

Industry experts predict that green building practices will become standard rather than exceptional within the next five years, particularly for high-end developments.',
                'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Sihanoukville Property Market Rebounds with Tourism Recovery',
                'category' => 'Market Update',
                'excerpt' => 'Coastal city sees renewed investor interest as international tourism returns to pre-pandemic levels. Beach-front properties and hospitality sector lead recovery.',
                'content' => 'Sihanoukville\'s property market is experiencing a significant rebound as international tourism returns to Cambodia\'s premier coastal destination. After several challenging years, the city is witnessing renewed investor confidence and development activity.

Tourism statistics from the Ministry of Tourism show that visitor arrivals to Sihanoukville have increased by 85% in 2025 compared to the previous year, with Chinese, Russian, and European tourists leading the recovery. This has directly impacted the local property market, particularly in the hospitality and residential sectors.

Beachfront properties are seeing the strongest demand, with average prices increasing by 12% year-over-year. Several international hotel chains have announced expansion plans, including the development of new five-star resorts along Otres Beach and Independence Beach.

"The fundamentals are strong again," says Mr. Chen Wei, a property analyst specializing in coastal markets. "Infrastructure improvements, including the expanded airport and improved road connections to Phnom Penh, have made Sihanoukville more accessible than ever."

The rental market is also showing positive signs, with occupancy rates for quality apartments and villas reaching 75%, up from just 40% two years ago. Short-term rental platforms report increasing bookings, suggesting growing confidence in the tourism sector.',
                'image_url' => 'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Digital Transformation Reshapes Cambodia Real Estate Transactions',
                'category' => 'Technology',
                'excerpt' => 'PropTech platforms revolutionize property buying and selling processes. Virtual tours and online transactions become mainstream in Cambodia\'s property market.',
                'content' => 'The Cambodian real estate industry is undergoing a digital revolution, with technology platforms transforming how properties are bought, sold, and managed. The adoption of PropTech solutions has accelerated dramatically in 2025, changing traditional business models.

Leading the charge are platforms like Realestate.com.kh and IPS Cambodia, which now offer virtual property tours, online document processing, and digital payment solutions. These innovations have made property transactions more efficient and transparent, attracting tech-savvy millennials to the market.

"We\'re seeing a fundamental shift in consumer behavior," explains Ms. Sarah Lim, CEO of PropTech Cambodia. "Buyers now expect to view properties virtually, compare prices online, and even complete transactions digitally. The pandemic accelerated this trend, but it\'s now become the new normal."

The government has supported this digital transformation by introducing e-signatures for property documents and online land title verification systems. These measures have reduced transaction times from weeks to days in some cases.

Real estate agencies are adapting by investing in digital marketing, virtual reality showrooms, and AI-powered property matching systems. A recent survey found that 78% of property buyers in Phnom Penh used online platforms as their primary research tool.

The trend is expected to continue, with blockchain technology for property records and AI-driven valuation tools on the horizon.',
                'image_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Affordable Housing Initiative Launches in Kampot Province',
                'category' => 'Social Housing',
                'excerpt' => 'Government partners with private developers to deliver 5,000 affordable homes. Program targets low-income families with subsidized loans and flexible payment terms.',
                'content' => 'The Cambodian government, in partnership with private developers, has launched an ambitious affordable housing initiative in Kampot Province aimed at providing homeownership opportunities for low-income families.

The program, which will deliver 5,000 homes over the next three years, offers houses priced between $15,000 and $35,000, with subsidized interest rates as low as 4% per annum. The Ministry of Land Management has allocated 200 hectares for the development, strategically located near employment centers and public services.

"This initiative represents our commitment to ensuring every Cambodian family has access to decent, affordable housing," said H.E. Chea Sophara, Minister of Land Management, Urban Planning and Construction. "We\'re working closely with financial institutions to provide accessible financing options."

The development will include essential infrastructure such as roads, electricity, water supply, and sewage systems. Community facilities including schools, health centers, and markets are also planned as part of the master development.

First-time homebuyers can benefit from additional incentives, including waived registration fees and a grace period for initial payments. The Asian Development Bank has provided technical assistance and partial funding for the project.

Early response has been overwhelmingly positive, with over 2,000 families already registering interest in the first phase of development.',
                'image_url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Luxury Villa Market Attracts Ultra-High Net Worth Buyers',
                'category' => 'Luxury Real Estate',
                'excerpt' => 'Premium properties in exclusive locations command record prices. International buyers drive demand for luxury estates in Phnom Penh and coastal areas.',
                'content' => 'Cambodia\'s luxury real estate segment is experiencing unprecedented growth, with ultra-high net worth individuals from across Asia showing increasing interest in premium properties.

Recent transactions in exclusive areas such as Koh Pich (Diamond Island) and the Embassy District have set new price records, with luxury villas selling for between $2 million and $8 million. These properties feature world-class amenities including private pools, smart home systems, and concierge services.

"We\'re seeing a new breed of luxury buyer," notes Mr. Philippe Martin, Director of Knight Frank Cambodia. "They want not just a property, but a lifestyle investment that offers privacy, security, and exceptional quality."

The trend extends beyond Phnom Penh, with coastal areas like Kep and the islands off Sihanoukville attracting buyers seeking exclusive beachfront estates. Several ultra-luxury resort developments are underway, targeting the growing market for vacation homes.

International buyers, particularly from Hong Kong, Singapore, and mainland China, represent approximately 60% of luxury property purchases. They\'re attracted by Cambodia\'s relatively low property prices compared to other regional markets, favorable foreign ownership laws, and potential for capital appreciation.

Developers are responding with increasingly sophisticated offerings, including branded residences managed by international hospitality groups and estates with private beach access.',
                'image_url' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Co-Working Spaces Transform Office Market Dynamics',
                'category' => 'Commercial',
                'excerpt' => 'Flexible workspace providers expand rapidly across Phnom Penh. Traditional office landlords adapt to changing tenant preferences post-pandemic.',
                'content' => 'The commercial office market in Cambodia is undergoing a fundamental transformation as co-working spaces proliferate across Phnom Penh, reshaping how businesses think about workspace.

International operators like WeWork, Regus, and local players such as Emerald Hub and Impact Hub have expanded aggressively, adding over 50,000 square meters of flexible workspace in 2025 alone. This represents a 60% increase in co-working capacity compared to the previous year.

"The demand for flexibility has never been higher," explains Ms. Jennifer Tang, Country Manager for WeWork Cambodia. "Companies want the ability to scale up or down quickly, and they value the networking opportunities that co-working spaces provide."

Traditional office landlords are adapting by offering more flexible lease terms and incorporating co-working elements into their buildings. Several Grade A office towers now dedicate entire floors to flexible workspace, catering to the hybrid work model that has become standard.

The trend is particularly popular among tech startups, creative agencies, and international companies testing the Cambodian market. Average occupancy rates for quality co-working spaces exceed 80%, with hot-desk memberships starting from $100 per month.

This shift is influencing office design trends, with emphasis on collaborative spaces, wellness facilities, and technology infrastructure becoming standard expectations.',
                'image_url' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Battambang Emerges as New Investment Hotspot',
                'category' => 'Regional Focus',
                'excerpt' => 'Cambodia\'s second-largest city attracts developer attention with planned economic zones and infrastructure upgrades. Property prices remain attractive for early investors.',
                'content' => 'Battambang is rapidly emerging as Cambodia\'s next property investment frontier, with major developers and investors turning their attention to the country\'s second-largest city.

The city\'s transformation is being driven by significant infrastructure investments, including the upcoming Phnom Penh-Battambang expressway and the development of new industrial zones aimed at manufacturing and agro-processing industries. These developments are expected to create thousands of jobs and drive demand for both residential and commercial properties.

Current property prices in Battambang remain highly attractive, with land in prime locations costing 70% less than comparable areas in Phnom Penh. This value proposition has attracted both local and international investors seeking higher returns.

"Battambang offers what Phnom Penh offered 10 years ago - tremendous growth potential at accessible prices," says Mr. Kuy Vat, a local property consultant. "We\'re seeing increasing inquiries from serious investors who understand the long-term opportunity."

Several major residential projects are already underway, including modern boreys and the city\'s first international-standard shopping complex. The local government has streamlined approval processes to encourage development while maintaining the city\'s cultural heritage.

Tourism also plays a role, with Battambang\'s colonial architecture and cultural attractions drawing increasing visitor numbers, spurring demand for boutique hotels and guesthouses.',
                'image_url' => 'https://images.unsplash.com/photo-1565953522043-baea26b83b7e?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Property Management Services Sector Expands Rapidly',
                'category' => 'Services',
                'excerpt' => 'Professional property management companies see 40% growth as owners recognize value of expert services. Technology integration drives efficiency and transparency.',
                'content' => 'Cambodia\'s property management sector is experiencing explosive growth as property owners increasingly recognize the value of professional management services in maintaining and enhancing property values.

The sector has grown by 40% in 2025, with both international and local property management firms expanding their portfolios. Companies are now managing everything from luxury condominiums to commercial complexes and industrial facilities.

"Professional property management is no longer a luxury but a necessity," states Mr. Richard Thompson, Regional Director of CBRE Property Management. "Owners understand that proper management directly impacts rental yields, occupancy rates, and long-term asset value."

Technology is playing a crucial role in the sector\'s evolution. Property management firms are implementing smart building systems, mobile apps for tenant communication, and predictive maintenance programs using IoT sensors. These innovations have improved operational efficiency and tenant satisfaction rates.

The growth has created significant employment opportunities, with demand for qualified property managers, facility engineers, and customer service professionals outpacing supply. Several educational institutions have launched property management certification programs to address this skills gap.

Service standards are also improving, with many firms now offering 24/7 support, multilingual staff, and comprehensive facility management including security, cleaning, and maintenance services.',
                'image_url' => 'https://images.unsplash.com/photo-1524634126442-357e0eac3c14?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(22),
            ],
            [
                'title' => 'REITs Introduction Could Transform Cambodia Investment Landscape',
                'category' => 'Investment',
                'excerpt' => 'Securities regulator drafts framework for Real Estate Investment Trusts. Market participants optimistic about new investment vehicle for retail investors.',
                'content' => 'The Securities and Exchange Regulator of Cambodia (SERC) has released a draft regulatory framework for Real Estate Investment Trusts (REITs), potentially opening a new chapter in the country\'s capital markets and property sector.

The proposed framework would allow property companies to list REITs on the Cambodia Securities Exchange, providing retail investors with access to real estate investments previously available only to institutional and high-net-worth individuals.

"REITs will democratize real estate investment in Cambodia," explains Dr. Sou Socheat, Director-General of SERC. "They will provide liquidity to the property market while offering investors a transparent, regulated investment vehicle with attractive dividend yields."

The framework includes provisions for different types of REITs, including those focused on commercial, residential, and industrial properties. Minimum dividend distribution requirements of 90% of net income are proposed to ensure attractive returns for investors.

Major property developers have expressed strong interest, with several already preparing potential REIT listings. Analysts estimate the Cambodian REIT market could reach $2 billion in assets under management within five years of launch.

The introduction of REITs is expected to attract more institutional investment into Cambodia\'s property sector, improve market transparency, and provide developers with alternative funding sources for new projects.

Public consultation on the framework continues until the end of the year, with the first REIT listings potentially possible by mid-2026.',
                'image_url' => 'https://images.unsplash.com/photo-1579532537598-459ecdaf39cc?w=800&h=600&fit=crop',
                'published_at' => now()->subDays(2),
            ],
        ];


        foreach ($articles as $articleData) {
            // Create the article with default English values
            $article = NewsArticle::create($articleData);

            // Add translations for all languages
            $locales = ['en', 'km', 'zh', 'fr'];

            // Get translatable fields that exist in the article data
            $translatableFields = ['title', 'category', 'excerpt', 'content'];

            // Translation mappings for each article field
            $translations = [
                'en' => [
                    'title' => $articleData['title'] ?? '',
                    'category' => $articleData['category'] ?? '',
                    'excerpt' => $articleData['excerpt'] ?? '',
                    'content' => $articleData['content'] ?? '',
                ],
                'km' => [
                    'title' => ($articleData['title'] ?? '') . ' (ខ្មែរ)',
                    'category' => $articleData['category'] ?? '',
                    'excerpt' => ($articleData['excerpt'] ?? '') . ' [សង្ខេបជាភាសាខ្មែរ]',
                    'content' => ($articleData['content'] ?? '') . "\n\n[មាតិកាពេញលេញជាភាសាខ្មែរ]",
                ],
                'zh' => [
                    'title' => ($articleData['title'] ?? '') . ' (中文)',
                    'category' => $articleData['category'] ?? '',
                    'excerpt' => ($articleData['excerpt'] ?? '') . ' [中文摘要]',
                    'content' => ($articleData['content'] ?? '') . "\n\n[完整中文内容]",
                ],
                'fr' => [
                    'title' => ($articleData['title'] ?? '') . ' (Français)',
                    'category' => $articleData['category'] ?? '',
                    'excerpt' => ($articleData['excerpt'] ?? '') . ' [Extrait en français]',
                    'content' => ($articleData['content'] ?? '') . "\n\n[Contenu complet en français]",
                ],
            ];

            // Set translations for each locale
            foreach ($locales as $locale) {
                if (isset($translations[$locale])) {
                    foreach ($translations[$locale] as $field => $value) {
                        if (!empty($value) && in_array($field, $translatableFields)) {
                            $article->setTranslation($field, $value, $locale);
                        }
                    }
                }
            }
        }

        $this->command->info('News articles seeded successfully with translations!');
    }
}
