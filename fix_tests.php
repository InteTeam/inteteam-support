<?php

$testDir = __DIR__ . '/tests';

// Recursively get all PHP files
function getPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

$files = getPhpFiles($testDir);
$fixedCount = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace expect('table')->toHaveInDatabase with $this->assertDatabaseHas('table'
    $content = preg_replace(
        "/expect\('([^']+)'\)->toHaveInDatabase\(/",
        "\$this->assertDatabaseHas('$1', ",
        $content
    );
    
    // Replace expect("table")->toHaveInDatabase with $this->assertDatabaseHas("table"
    $content = preg_replace(
        '/expect\("([^"]+)"\)->toHaveInDatabase\(/',
        '$this->assertDatabaseHas("$1", ',
        $content
    );
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
        $fixedCount++;
    }
}

echo "\nFixed $fixedCount files!\n";
