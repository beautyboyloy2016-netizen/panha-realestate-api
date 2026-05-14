<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadSeederImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seeder:download-images
                            {--force : Overwrite existing images}
                            {--dry-run : Show what would be downloaded without actually downloading}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download all Unsplash images from PropertySeeder for local use';

    /**
     * All image URLs from PropertySeeder
     */
    protected array $imageUrls = [
        'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1605276374104-dee2a0ed3cd6?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1556912173-3bb406ef7e77?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1566908829550-e6551b00979b?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1558036117-15d82a90b9b1?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560185007-c5ca9d2c014d?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1523217582562-09d0def993a6?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1599427303058-f04cbcf4756f?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560448075-bb485b067938?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1540932239986-30128078f3c5?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1565623006066-82f23c79210b?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1602941525421-8f8b81d3edbb?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1556020685-ae41abfc9365?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1580216643062-cf460548a66a?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1449844908441-8829872d2607?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1567496898669-ee935f5f647a?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1464146072230-91cabc968266?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1605146769289-440113cc3d00?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1515263487990-61b07816b324?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1448630360428-65456885c650?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1621619856624-42fd193a0661?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1500076656116-558758c991c1?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1540202403-b7abd6747a18?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1516156008625-3a9d6067fab5?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1494526585095-c41746248156?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1479839672679-a46483c0e7c8?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1555636222-cae831e670b3?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storagePath = storage_path('app/public/seeder-images');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Get unique URLs
        $uniqueUrls = array_unique($this->imageUrls);

        $this->info('');
        $this->info('🖼️  Property Seeder Image Downloader');
        $this->info('=====================================');
        $this->info("Total unique images: " . count($uniqueUrls));
        $this->info("Storage path: {$storagePath}");
        $this->info('');

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will be downloaded');
            $this->info('');
        }

        // Create directory if not exists
        if (!$isDryRun && !File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
            $this->info("📁 Created directory: {$storagePath}");
        }

        $progressBar = $this->output->createProgressBar(count($uniqueUrls));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->start();

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;
        $mapping = [];

        foreach ($uniqueUrls as $url) {
            $filename = $this->getFilenameFromUrl($url);
            $localPath = "{$storagePath}/{$filename}";
            $publicUrl = "/storage/seeder-images/{$filename}";

            $progressBar->setMessage($filename);

            // Check if file already exists
            if (!$force && File::exists($localPath)) {
                $skipped++;
                $mapping[$url] = $publicUrl;
                $progressBar->advance();
                continue;
            }

            if ($isDryRun) {
                $mapping[$url] = $publicUrl;
                $downloaded++;
                $progressBar->advance();
                continue;
            }

            // Download the image
            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    File::put($localPath, $response->body());
                    $mapping[$url] = $publicUrl;
                    $downloaded++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->error("Failed to download: {$url} (HTTP {$response->status()})");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Error downloading {$url}: " . $e->getMessage());
            }

            $progressBar->advance();

            // Small delay to be nice to Unsplash
            usleep(100000); // 100ms
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📊 Summary:');
        $this->info("   ✅ Downloaded: {$downloaded}");
        $this->info("   ⏭️  Skipped (existing): {$skipped}");
        if ($failed > 0) {
            $this->error("   ❌ Failed: {$failed}");
        }
        $this->newLine();

        // Generate URL mapping file
        $mappingPath = $storagePath . '/url-mapping.json';
        if (!$isDryRun) {
            File::put($mappingPath, json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("📄 URL mapping saved to: {$mappingPath}");
        }

        $this->newLine();
        $this->info('💡 To use local images in PropertySeeder:');
        $this->info('   1. Run: php artisan storage:link (if not already done)');
        $this->info('   2. Update PropertySeeder to use local URLs from url-mapping.json');
        $this->info('   3. Or use the helper: $this->getLocalImageUrl($unsplashUrl)');
        $this->newLine();

        // Show example code
        $this->info('📝 Example code to add to PropertySeeder:');
        $this->line('');
        $this->line('    protected function getLocalImageUrl(string $unsplashUrl): string');
        $this->line('    {');
        $this->line('        $mappingFile = storage_path(\'app/public/seeder-images/url-mapping.json\');');
        $this->line('        if (file_exists($mappingFile)) {');
        $this->line('            $mapping = json_decode(file_get_contents($mappingFile), true);');
        $this->line('            return $mapping[$unsplashUrl] ?? $unsplashUrl;');
        $this->line('        }');
        $this->line('        return $unsplashUrl;');
        $this->line('    }');
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Extract filename from Unsplash URL
     */
    protected function getFilenameFromUrl(string $url): string
    {
        // Extract the photo ID from URL like: photo-1613490493576-7fde63acd811
        if (preg_match('/photo-([a-zA-Z0-9-]+)/', $url, $matches)) {
            return 'property-' . $matches[1] . '.jpg';
        }

        // Fallback to hash
        return 'property-' . md5($url) . '.jpg';
    }
}
