<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site.name',
                'value' => 'Panha Real Estate',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Website name',
                'is_public' => true
            ],
            [
                'key' => 'site.tagline',
                'value' => 'Find Your Dream Property in Cambodia',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Website tagline/slogan',
                'is_public' => true
            ],
            [
                'key' => 'site.logo',
                'value' => '/storage/logo.png',
                'type' => 'file',
                'group' => 'general',
                'description' => 'Website logo path',
                'is_public' => true
            ],
            [
                'key' => 'site.favicon',
                'value' => '/storage/favicon.ico',
                'type' => 'file',
                'group' => 'general',
                'description' => 'Website favicon path',
                'is_public' => true
            ],
            [
                'key' => 'site.description',
                'value' => 'Cambodia\'s leading real estate platform for buying, selling, and renting properties',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Website description',
                'is_public' => true
            ],
            [
                'key' => 'contact.email',
                'value' => 'info@panhaestate.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Contact email address',
                'is_public' => true
            ],
            [
                'key' => 'contact.phone',
                'value' => '+855 12 345 678',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Contact phone number',
                'is_public' => true
            ],
            [
                'key' => 'contact.address',
                'value' => 'Phnom Penh, Cambodia',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Contact address',
                'is_public' => true
            ],
            [
                'key' => 'social.facebook',
                'value' => 'https://facebook.com/panhaestate',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Facebook page URL',
                'is_public' => true
            ],
            [
                'key' => 'social.instagram',
                'value' => 'https://instagram.com/panhaestate',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Instagram profile URL',
                'is_public' => true
            ],
            [
                'key' => 'social.telegram',
                'value' => 'https://t.me/panhaestate',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Telegram channel URL',
                'is_public' => true
            ],

            // Currency & Localization
            [
                'key' => 'currency.default',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Default currency code',
                'is_public' => true
            ],
            [
                'key' => 'currency.secondary',
                'value' => 'KHR',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Secondary currency code',
                'is_public' => true
            ],
            [
                'key' => 'currency.exchange_rate',
                'value' => '4100',
                'type' => 'integer',
                'group' => 'localization',
                'description' => 'KHR per 1 USD exchange rate',
                'is_public' => true
            ],
            [
                'key' => 'currency.symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Currency symbol',
                'is_public' => true
            ],
            [
                'key' => 'currency.position',
                'value' => 'before',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Currency symbol position (before/after)',
                'is_public' => true
            ],
            [
                'key' => 'language.default',
                'value' => 'en',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Default language',
                'is_public' => true
            ],
            [
                'key' => 'language.available',
                'value' => json_encode(['en', 'km', 'zh', 'fr']),
                'type' => 'json',
                'group' => 'localization',
                'description' => 'Available languages',
                'is_public' => true
            ],

            // Language Settings
            [
                'key' => 'language.en.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'languages',
                'description' => 'Enable English language',
                'is_public' => true
            ],
            [
                'key' => 'language.en.name',
                'value' => 'English',
                'type' => 'string',
                'group' => 'languages',
                'description' => 'English language display name',
                'is_public' => true
            ],
            [
                'key' => 'language.km.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'languages',
                'description' => 'Enable Khmer language',
                'is_public' => true
            ],
            [
                'key' => 'language.km.name',
                'value' => 'ភាសាខ្មែរ',
                'type' => 'string',
                'group' => 'languages',
                'description' => 'Khmer language display name',
                'is_public' => true
            ],
            [
                'key' => 'language.zh.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'languages',
                'description' => 'Enable Chinese language',
                'is_public' => true
            ],
            [
                'key' => 'language.zh.name',
                'value' => '中文',
                'type' => 'string',
                'group' => 'languages',
                'description' => 'Chinese language display name',
                'is_public' => true
            ],
            [
                'key' => 'language.fr.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'languages',
                'description' => 'Enable French language',
                'is_public' => true
            ],
            [
                'key' => 'language.fr.name',
                'value' => 'Français',
                'type' => 'string',
                'group' => 'languages',
                'description' => 'French language display name',
                'is_public' => true
            ],
            [
                'key' => 'timezone.default',
                'value' => 'Asia/Phnom_Penh',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Default timezone',
                'is_public' => true
            ],
            [
                'key' => 'date.format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'group' => 'localization',
                'description' => 'Date format',
                'is_public' => true
            ],

            // Authentication Settings
            [
                'key' => 'auth.social.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable/disable all social authentication',
                'is_public' => true
            ],
            [
                'key' => 'auth.google.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable/disable Google login',
                'is_public' => true
            ],
            [
                'key' => 'auth.telegram.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable/disable Telegram login',
                'is_public' => true
            ],
            [
                'key' => 'auth.registration.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable/disable user registration',
                'is_public' => true
            ],
            [
                'key' => 'auth.email_verification.required',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Require email verification for new users',
                'is_public' => false
            ],

            // Property Settings
            [
                'key' => 'property.listing_duration',
                'value' => '90',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Default listing duration in days',
                'is_public' => false
            ],
            [
                'key' => 'property.max_images',
                'value' => '20',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Maximum images per property',
                'is_public' => true
            ],
            [
                'key' => 'property.max_videos',
                'value' => '5',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Maximum videos per property',
                'is_public' => true
            ],
            [
                'key' => 'property.featured_price',
                'value' => '50',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Price for featured listing (USD)',
                'is_public' => true
            ],
            [
                'key' => 'property.premium_price',
                'value' => '100',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Price for premium listing (USD)',
                'is_public' => true
            ],
            [
                'key' => 'property.auto_renew',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'property',
                'description' => 'Auto-renew listings',
                'is_public' => false
            ],
            [
                'key' => 'property.moderation_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'property',
                'description' => 'Enable property moderation',
                'is_public' => false
            ],
            [
                'key' => 'property.min_price',
                'value' => '1000',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Minimum property price',
                'is_public' => true
            ],
            [
                'key' => 'property.max_price',
                'value' => '10000000',
                'type' => 'integer',
                'group' => 'property',
                'description' => 'Maximum property price',
                'is_public' => true
            ],

            // SEO Settings
            [
                'key' => 'seo.meta_title',
                'value' => 'Panha Real Estate - Properties in Cambodia',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta title',
                'is_public' => true
            ],
            [
                'key' => 'seo.meta_description',
                'value' => 'Find and list properties for sale and rent in Cambodia. Explore apartments, houses, condos, and commercial properties.',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta description',
                'is_public' => true
            ],
            [
                'key' => 'seo.meta_keywords',
                'value' => 'real estate, property, Cambodia, Phnom Penh, buy, rent, sell',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta keywords',
                'is_public' => true
            ],
            [
                'key' => 'seo.google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Analytics tracking ID',
                'is_public' => true
            ],
            [
                'key' => 'seo.sitemap_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'seo',
                'description' => 'Enable sitemap generation',
                'is_public' => false
            ],

            // Media Settings
            [
                'key' => 'media.max_upload_size',
                'value' => '10240',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Max upload size in KB (10MB)',
                'is_public' => true
            ],
            [
                'key' => 'media.allowed_extensions',
                'value' => json_encode(['jpg', 'jpeg', 'png', 'gif', 'webp']),
                'type' => 'json',
                'group' => 'media',
                'description' => 'Allowed file extensions',
                'is_public' => true
            ],
            [
                'key' => 'media.image_quality',
                'value' => '85',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Image compression quality (1-100)',
                'is_public' => false
            ],
            [
                'key' => 'media.thumbnail_width',
                'value' => '300',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Thumbnail width in pixels',
                'is_public' => false
            ],
            [
                'key' => 'media.thumbnail_height',
                'value' => '300',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Thumbnail height in pixels',
                'is_public' => false
            ],

            // Map Settings
            [
                'key' => 'map.provider',
                'value' => 'google',
                'type' => 'string',
                'group' => 'map',
                'description' => 'Map provider (google/mapbox/leaflet)',
                'is_public' => true
            ],
            [
                'key' => 'map.google_api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'map',
                'description' => 'Google Maps API key',
                'is_public' => false
            ],
            [
                'key' => 'map.default_lat',
                'value' => '11.5564',
                'type' => 'float',
                'group' => 'map',
                'description' => 'Default map latitude (Phnom Penh)',
                'is_public' => true
            ],
            [
                'key' => 'map.default_lng',
                'value' => '104.9282',
                'type' => 'float',
                'group' => 'map',
                'description' => 'Default map longitude (Phnom Penh)',
                'is_public' => true
            ],
            [
                'key' => 'map.default_zoom',
                'value' => '12',
                'type' => 'integer',
                'group' => 'map',
                'description' => 'Default map zoom level',
                'is_public' => true
            ],

            // Email Settings
            [
                'key' => 'mail.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'mail',
                'description' => 'Enable email notifications',
                'is_public' => false
            ],
            [
                'key' => 'mail.from_name',
                'value' => 'Panha Real Estate',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Email sender name',
                'is_public' => false
            ],
            [
                'key' => 'mail.from_address',
                'value' => 'noreply@panhaestate.com',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Email sender address',
                'is_public' => false
            ],

            // Security Settings
            [
                'key' => 'security.recaptcha_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable reCAPTCHA',
                'is_public' => true
            ],
            [
                'key' => 'security.rate_limit.api',
                'value' => '60',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'API rate limit (requests per minute)',
                'is_public' => false
            ],

            // Authentication Settings
            [
                'key' => 'auth.allow_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Allow new user registration',
                'is_public' => true
            ],
            [
                'key' => 'auth.require_email_verification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Require email verification for new accounts',
                'is_public' => true
            ],
            [
                'key' => 'auth.password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'authentication',
                'description' => 'Minimum password length',
                'is_public' => true
            ],
            [
                'key' => 'auth.session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'group' => 'authentication',
                'description' => 'Session lifetime in minutes',
                'is_public' => false
            ],
            [
                'key' => 'auth.max_login_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'authentication',
                'description' => 'Maximum login attempts before lockout',
                'is_public' => false
            ],
            [
                'key' => 'auth.lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'group' => 'authentication',
                'description' => 'Account lockout duration in minutes',
                'is_public' => false
            ],

            // Social Authentication - Google
            [
                'key' => 'auth.google.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable Google OAuth login',
                'is_public' => true
            ],
            [
                'key' => 'auth.google.client_id',
                'value' => env('GOOGLE_CLIENT_ID', ''),
                'type' => 'string',
                'group' => 'authentication',
                'description' => 'Google OAuth Client ID',
                'is_public' => false
            ],
            [
                'key' => 'auth.google.client_secret',
                'value' => env('GOOGLE_CLIENT_SECRET', ''),
                'type' => 'string',
                'group' => 'authentication',
                'description' => 'Google OAuth Client Secret',
                'is_public' => false
            ],
            [
                'key' => 'auth.google.redirect_url',
                'value' => env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')),
                'type' => 'string',
                'group' => 'authentication',
                'description' => 'Google OAuth Redirect URL',
                'is_public' => false
            ],

            // Social Authentication - Telegram
            [
                'key' => 'auth.telegram.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable Telegram login',
                'is_public' => true
            ],
            [
                'key' => 'auth.telegram.bot_token',
                'value' => env('TELEGRAM_BOT_TOKEN', ''),
                'type' => 'string',
                'group' => 'authentication',
                'description' => 'Telegram Bot Token',
                'is_public' => false
            ],
            [
                'key' => 'auth.telegram.bot_username',
                'value' => env('TELEGRAM_BOT_USERNAME', ''),
                'type' => 'string',
                'group' => 'authentication',
                'description' => 'Telegram Bot Username',
                'is_public' => true
            ],

            // Two-Factor Authentication
            [
                'key' => 'auth.two_factor.enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Enable two-factor authentication',
                'is_public' => true
            ],
            [
                'key' => 'auth.two_factor.required',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'authentication',
                'description' => 'Require two-factor authentication for all users',
                'is_public' => false
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
