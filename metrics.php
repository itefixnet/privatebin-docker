<?php
define("PATH", "/srv/privatebin/");
require PATH . "lib/Controller.php";
require PATH . "lib/Data/AbstractData.php";
require PATH . "lib/Data/Filesystem.php";
require PATH . "lib/Model.php";
require PATH . "lib/Model/Paste.php";

header("Content-Type: text/plain; version=0.0.4");

$config = new PrivateBin\Configuration;
$data = new PrivateBin\Data\Filesystem(array("dir" => PATH . "data"));
$model = new PrivateBin\Model\Paste($config, $data);
$stats = $model->getStatistics();

// Basic paste statistics
echo "# HELP privatebin_pastes_total Total number of pastes\n";
echo "# TYPE privatebin_pastes_total gauge\n";
echo "privatebin_pastes_total " . ($stats["total"] ?? 0) . "\n";

echo "# HELP privatebin_pastes_expired Number of expired pastes\n";
echo "# TYPE privatebin_pastes_expired gauge\n";
echo "privatebin_pastes_expired " . ($stats["expired"] ?? 0) . "\n";

echo "# HELP privatebin_pastes_burn_after_reading Number of burn after reading pastes\n";
echo "# TYPE privatebin_pastes_burn_after_reading gauge\n";
echo "privatebin_pastes_burn_after_reading " . ($stats["burnafterreading"] ?? 0) . "\n";

echo "# HELP privatebin_discussions_total Number of discussions\n";
echo "# TYPE privatebin_discussions_total gauge\n";
echo "privatebin_discussions_total " . ($stats["discussions"] ?? 0) . "\n";

// Paste format statistics
echo "# HELP privatebin_pastes_plaintext Number of plain text pastes\n";
echo "# TYPE privatebin_pastes_plaintext gauge\n";
echo "privatebin_pastes_plaintext " . ($stats["plaintextpaste"] ?? 0) . "\n";

echo "# HELP privatebin_pastes_sourcecode Number of source code pastes\n";
echo "# TYPE privatebin_pastes_sourcecode gauge\n";
echo "privatebin_pastes_sourcecode " . ($stats["codepaste"] ?? 0) . "\n";

echo "# HELP privatebin_pastes_markdown Number of markdown pastes\n";
echo "# TYPE privatebin_pastes_markdown gauge\n";
echo "privatebin_pastes_markdown " . ($stats["markdownpaste"] ?? 0) . "\n";

// Calculate storage size
$dataDir = PATH . "data";
$totalSize = 0;
$fileCount = 0;

if (is_dir($dataDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dataDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $totalSize += $file->getSize();
            $fileCount++;
        }
    }
}

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
