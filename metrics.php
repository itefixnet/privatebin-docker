<?php
define("PATH", "/srv/privatebin/");
require PATH . "lib/Configuration.php";
require PATH . "lib/Data/AbstractData.php";
require PATH . "lib/Data/Filesystem.php";

header("Content-Type: text/plain; version=0.0.4");

// Initialize data storage
$config = new PrivateBin\Configuration;
$store = new PrivateBin\Data\Filesystem(array("dir" => PATH . "data"));

// Get paste statistics by scanning the data directory
$dataDir = PATH . "data";
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
    $dirs = scandir($dataDir);
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        
        $subDir = $dataDir . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($subDir)) continue;
        
        $files = scandir($subDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $subDir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($filePath)) continue;
            
            $stats['total']++;
            $totalSize += filesize($filePath);
            $fileCount++;
            
            // Read paste metadata
            $data = json_decode(file_get_contents($filePath), true);
            if ($data && is_array($data)) {
                // Check if expired
                if (isset($data['meta']['expire_date'])) {
                    if ($data['meta']['expire_date'] < time()) {
                        $stats['expired']++;
                    }
                }
                
                // Check burn after reading
                if (isset($data['meta']['burnafterreading']) && $data['meta']['burnafterreading']) {
                    $stats['burnafterreading']++;
                }
                
                // Check for discussions
                if (isset($data['meta']['opendiscussion']) && $data['meta']['opendiscussion']) {
                    $stats['discussions']++;
                }
                
                // Check format
                if (isset($data['meta']['formatter'])) {
                    switch ($data['meta']['formatter']) {
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

