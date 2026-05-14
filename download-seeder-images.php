#!/usr/bin/env php
<?php

/**
 * Standalone script to download all seeder images (Property, Project, NewsArticle)
 *
 * Usage:
 *   php download-seeder-images.php
 *   php download-seeder-images.php --force    (overwrite existing)
 *   php download-seeder-images.php --dry-run  (preview only)
 */

// ============================================================================
// Image URLs from PropertySeeder
// ============================================================================
$propertyImages = [
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

// ============================================================================
// Image URLs from ProjectSeeder
// ============================================================================
$projectImages = [
    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800',
    'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
    'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800',
    'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=800',
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800',
];

// ============================================================================
// Image URLs from NewsArticleSeeder
// ============================================================================
$newsImages = [
    'https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1460472178825-e5240623afd5?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1554469384-e58fac16e23a?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1506197603052-3cc9c3a201bd?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1565953522043-baea26b83b7e?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1524634126442-357e0eac3c14?w=800&h=600&fit=crop',
    'https://images.unsplash.com/photo-1579532537598-459ecdaf39cc?w=800&h=600&fit=crop',
];

// Merge all image URLs
// $imageUrls = array_merge($propertyImages, $projectImages, $newsImages);
$imageUrls = array_merge($newsImages);

// Parse command line arguments
$force = in_array('--force', $argv);
$dryRun = in_array('--dry-run', $argv);

// Configuration
$baseDir = __DIR__;
$storagePath = $baseDir . '/storage/app/public/seeder-images';
$publicPath = '/storage/seeder-images';

// Colors for console output
function colorize($text, $color) {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m",
        'bold' => "\033[1m",
    ];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function printLine($text = '') {
    echo $text . PHP_EOL;
}

function printSuccess($text) {
    printLine(colorize('✅ ' . $text, 'green'));
}

function printError($text) {
    printLine(colorize('❌ ' . $text, 'red'));
}

function printInfo($text) {
    printLine(colorize('ℹ️  ' . $text, 'blue'));
}

function printWarning($text) {
    printLine(colorize('⚠️  ' . $text, 'yellow'));
}

/**
 * Extract filename from Unsplash URL
 */
function getFilenameFromUrl($url) {
    if (preg_match('/photo-([a-zA-Z0-9-]+)/', $url, $matches)) {
        return 'property-' . $matches[1] . '.jpg';
    }
    return 'property-' . md5($url) . '.jpg';
}

/**
 * Download file using cURL
 */
function downloadFile($url, $destination) {
    $ch = curl_init($url);
    $fp = fopen($destination, 'wb');

    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);

    $success = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);
    fclose($fp);

    if (!$success || $httpCode !== 200) {
        @unlink($destination);
        return ['success' => false, 'error' => $error ?: "HTTP {$httpCode}"];
    }

    return ['success' => true];
}

// Start script
printLine();
printLine(colorize('🖼️  Property Seeder Image Downloader', 'bold'));
printLine('=====================================');
printLine();

$uniqueUrls = array_unique($imageUrls);
printInfo("Total unique images: " . count($uniqueUrls));
printInfo("Storage path: {$storagePath}");
printLine();

if ($dryRun) {
    printWarning('DRY RUN MODE - No files will be downloaded');
    printLine();
}

// Create directory
if (!$dryRun && !is_dir($storagePath)) {
    if (!mkdir($storagePath, 0755, true)) {
        printError("Failed to create directory: {$storagePath}");
        exit(1);
    }
    printSuccess("Created directory: {$storagePath}");
}

$downloaded = 0;
$skipped = 0;
$failed = 0;
$mapping = [];
$total = count($uniqueUrls);
$current = 0;

foreach ($uniqueUrls as $url) {
    $current++;
    $filename = getFilenameFromUrl($url);
    $localPath = "{$storagePath}/{$filename}";
    $publicUrl = "{$publicPath}/{$filename}";

    $progress = str_pad($current, strlen($total), ' ', STR_PAD_LEFT);
    echo "\r[{$progress}/{$total}] Processing: {$filename}";

    // Check if file exists
    if (!$force && file_exists($localPath)) {
        $skipped++;
        $mapping[$url] = $publicUrl;
        continue;
    }

    if ($dryRun) {
        $mapping[$url] = $publicUrl;
        $downloaded++;
        continue;
    }

    // Download
    $result = downloadFile($url, $localPath);

    if ($result['success']) {
        $mapping[$url] = $publicUrl;
        $downloaded++;
    } else {
        $failed++;
        printLine();
        printError("Failed: {$filename} - {$result['error']}");
    }

    // Small delay
    usleep(100000); // 100ms
}

printLine();
printLine();
printLine(colorize('📊 Summary:', 'bold'));
printSuccess("Downloaded: {$downloaded}");
printInfo("Skipped (existing): {$skipped}");
if ($failed > 0) {
    printError("Failed: {$failed}");
}
printLine();

// Save mapping file
if (!$dryRun) {
    $mappingPath = $storagePath . '/url-mapping.json';
    file_put_contents($mappingPath, json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    printSuccess("URL mapping saved to: {$mappingPath}");
}

printLine();
printLine(colorize('💡 Next Steps:', 'bold'));
printLine('   1. Run: php artisan storage:link');
printLine('   2. Images will be accessible at: /storage/seeder-images/');
printLine('   3. Update PropertySeeder to use local URLs');
printLine();

// Generate helper code
printLine(colorize('📝 Add this helper to PropertySeeder:', 'bold'));
printLine();
printLine('    protected function getLocalImageUrl(string $url): string');
printLine('    {');
printLine('        $mapping = storage_path(\'app/public/seeder-images/url-mapping.json\');');
printLine('        if (file_exists($mapping)) {');
printLine('            $map = json_decode(file_get_contents($mapping), true);');
printLine('            return $map[$url] ?? $url;');
printLine('        }');
printLine('        return $url;');
printLine('    }');
printLine();

exit($failed > 0 ? 1 : 0);
