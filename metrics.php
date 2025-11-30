<?php
// Simplified metrics endpoint that doesn't require PrivateBin classes
header("Content-Type: text/plain; version=0.0.4");

// Data directory is always at /srv/privatebin/data
$dataDir = "/srv/privatebin/data";

// Debug: Check if directory is readable
if (!is_readable($dataDir)) {
    error_log("metrics.php: data directory not readable: " . $dataDir);
}

$stats = array(
    'total' => 0,
    'expired' => 0,
    'burnafterreading' => 0,
    'discussions' => 0,
    'plaintextpaste' => 0,
    'codepaste' => 0,
    'markdownpaste' => 0
);
$totalSize = 0;
$fileCount = 0;

if (is_dir($dataDir)) {
    // PrivateBin stores files in nested 2-character subdirectories (e.g., data/00/4d/004d177052bb2da7.php)
    $level1Dirs = @scandir($dataDir);
    if ($level1Dirs === false) {
        $level1Dirs = array();
    }
    
    foreach ($level1Dirs as $level1) {
        if ($level1 === '.' || $level1 === '..' || $level1 === '.htaccess') continue;
        
        $level1Path = $dataDir . DIRECTORY_SEPARATOR . $level1;
        if (!is_dir($level1Path)) continue;
        
        // Scan second level directories
        $level2Dirs = @scandir($level1Path);
        if ($level2Dirs === false) continue;
        
        foreach ($level2Dirs as $level2) {
            if ($level2 === '.' || $level2 === '..' || $level2 === '.htaccess') continue;
            
            $level2Path = $level1Path . DIRECTORY_SEPARATOR . $level2;
            if (!is_dir($level2Path)) continue;
            
            // Scan files in the deepest level
            $files = @scandir($level2Path);
            if ($files === false) continue;
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.htaccess') continue;
                
                $filePath = $level2Path . DIRECTORY_SEPARATOR . $file;
                if (!is_file($filePath)) continue;
                
                $stats['total']++;
                $fileSize = @filesize($filePath);
                if ($fileSize !== false) {
                    $totalSize += $fileSize;
                }
                $fileCount++;
                
                // Read paste metadata
                $content = @file_get_contents($filePath);
                if ($content === false) {
                    continue;
                }
                
                // Extract JSON from PHP file (format: <?php http_response_code(403); /* JSON */ )
                if (preg_match('/\/\*\s*(\{.*\})\s*$/', $content, $matches)) {
                    $data = json_decode($matches[1], true);
                    if ($data && is_array($data) && isset($data['meta'])) {
                        $meta = $data['meta'];
                        
                        // Check if expired
                        if (isset($meta['expire_date'])) {
                            if ($meta['expire_date'] < time()) {
                                $stats['expired']++;
                            }
                        }
                        
                        // Check burn after reading - stored in adata array
                        if (isset($data['adata']) && is_array($data['adata'])) {
                            // adata format: [encryption_params, formatter, opendiscussion, burnafterreading]
                            if (isset($data['adata'][2]) && $data['adata'][2]) {
                                $stats['discussions']++;
                            }
                            if (isset($data['adata'][3]) && $data['adata'][3]) {
                                $stats['burnafterreading']++;
                            }
                            // Formatter is in adata[1]
                            if (isset($data['adata'][1])) {
                                switch ($data['adata'][1]) {
                                    case 'plaintext':
                                        $stats['plaintextpaste']++;
                                        break;
                                    case 'syntaxhighlighting':
                                        $stats['codepaste']++;
                                        break;
                                    case 'markdown':
                                        $stats['markdownpaste']++;
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
// Basic paste statistics
echo "# HELP privatebin_pastes_total Total number of pastes\n";
echo "# TYPE privatebin_pastes_total gauge\n";
echo "privatebin_pastes_total " . $stats['total'] . "\n";

echo "# HELP privatebin_pastes_expired Number of expired pastes\n";
echo "# TYPE privatebin_pastes_expired gauge\n";
echo "privatebin_pastes_expired " . $stats['expired'] . "\n";

echo "# HELP privatebin_pastes_burn_after_reading Number of burn after reading pastes\n";
echo "# TYPE privatebin_pastes_burn_after_reading gauge\n";
echo "privatebin_pastes_burn_after_reading " . $stats['burnafterreading'] . "\n";

echo "# HELP privatebin_discussions_total Number of discussions\n";
echo "# TYPE privatebin_discussions_total gauge\n";
echo "privatebin_discussions_total " . $stats['discussions'] . "\n";

// Paste format statistics
echo "# HELP privatebin_pastes_plaintext Number of plain text pastes\n";
echo "# TYPE privatebin_pastes_plaintext gauge\n";
echo "privatebin_pastes_plaintext " . $stats['plaintextpaste'] . "\n";

echo "# HELP privatebin_pastes_sourcecode Number of source code pastes\n";
echo "# TYPE privatebin_pastes_sourcecode gauge\n";
echo "privatebin_pastes_sourcecode " . $stats['codepaste'] . "\n";

echo "# HELP privatebin_pastes_markdown Number of markdown pastes\n";
echo "# TYPE privatebin_pastes_markdown gauge\n";
echo "privatebin_pastes_markdown " . $stats['markdownpaste'] . "\n";

// Storage statistics
echo "# HELP privatebin_storage_bytes Total storage used in bytes\n";
echo "# TYPE privatebin_storage_bytes gauge\n";
echo "privatebin_storage_bytes " . $totalSize . "\n";

echo "# HELP privatebin_storage_files Total number of files in data directory\n";
echo "# TYPE privatebin_storage_files gauge\n";
echo "privatebin_storage_files " . $fileCount . "\n";

if ($fileCount > 0) {
    echo "# HELP privatebin_storage_average_file_bytes Average file size in bytes\n";
    echo "# TYPE privatebin_storage_average_file_bytes gauge\n";
    echo "privatebin_storage_average_file_bytes " . round($totalSize / $fileCount) . "\n";
}

