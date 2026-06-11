#!/usr/bin/env php
<?php

/**
 * Next.js to Inertia.js Migration Script
 * 
 * Automatically converts bro-next components to work with InteTeam CRM (Laravel + Inertia)
 */

$componentsDir = __DIR__ . '/resources/js/Components';

$migrations = [
    // Remove 'use client' directives
    "/^'use client'[\r\n]+/m" => '',
    "/^\"use client\"[\r\n]+/m" => '',
    
    // Replace Next.js Link with Inertia Link
    "/import\s+Link\s+from\s+['\"]next\/link['\"]/m" => "import { Link } from '@inertiajs/react'",
    
    // Replace Next.js useRouter with Inertia router
    "/import\s+\{\s*useRouter\s*\}\s+from\s+['\"]next\/navigation['\"]/m" => "import { router } from '@inertiajs/react'",
    
    // Replace Next.js usePathname with Inertia usePage
    "/import\s+\{\s*usePathname\s*\}\s+from\s+['\"]next\/navigation['\"]/m" => "import { usePage } from '@inertiajs/react'",
    
    // Replace Next.js Image with standard img (removed - handled separately)
    
    // Fix import paths (@ alias should point to resources/js)
    "/@\/components\//i" => "@/Components/",
    "/@\/lib\//i" => "@/lib/",
    "/@\/actions\//i" => "@/actions/",
    
    // Remove Next.js specific functions
    "/from\s+['\"]next\/cache['\"]/m" => "// Cache not needed in Inertia",
    "/from\s+['\"]next\/headers['\"]/m" => "// Headers handled by Laravel",
];

function migrateFile($filePath, $migrations) {
    $content = file_get_contents($filePath);
    $original = $content;
    
    foreach ($migrations as $pattern => $replacement) {
        $result = preg_replace($pattern, $replacement, $content);
        if ($result !== null) {
            $content = $result;
        }
    }
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        return true;
    }
    
    return false;
}

function getAllFiles($dir, $extension = 'tsx') {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === $extension) {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Get all TSX files
$files = getAllFiles($componentsDir);
$migratedCount = 0;

echo "Starting migration of " . count($files) . " files...\n\n";

foreach ($files as $file) {
    if (migrateFile($file, $migrations)) {
        $migratedCount++;
        echo "✓ " . basename($file) . "\n";
    }
}

echo "\n";
echo "Migration complete!\n";
echo "Files migrated: $migratedCount/" . count($files) . "\n";
echo "\nManual steps remaining:\n";
echo "1. Replace useRouter() calls with router.visit()\n";
echo "2. Replace usePathname() calls with usePage().url\n";
echo "3. Update API calls to use Inertia forms or axios\n";
echo "4. Test components work with Laravel props\n";
