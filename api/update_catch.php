<?php
$dir = new RecursiveDirectoryIterator(__DIR__);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/', RegexIterator::GET_MATCH);

foreach ($files as $fileInfo) {
    $file = $fileInfo[0];
    if (strpos($file, 'vendor') !== false) continue;

    $content = file_get_contents($file);
    
    // Pattern to find standard error JSON encoding in catch blocks and add the trace
    // Only modify if it doesn't have trace already
    $pattern = '/("error"\s*=>\s*\$e->getMessage\(\)\s*)\n\s*\]\);/i';
    $replacement = "$1,\n        \"trace\" => \$e->getTraceAsString()\n    ]);";
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    // Some blocks might look like "error" => $e->getMessage() without the comma or with different formatting
    // Let's also create a global exception handler in init.php
    
    if ($newContent !== null && $newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Updated: $file\n";
    }
}
?>
