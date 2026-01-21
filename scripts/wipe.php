<?php
require_once __DIR__ . "/../src/connection.php";
require_once __DIR__ . "/../src/pib/Thread.php";
require_once __DIR__ . "/../src/pib/Comment.php";
require_once __DIR__ . "/../src/pib/FileUpload.php";

if (php_sapi_name() !== 'cli') {
    die("ERROR: This script can only be run from command line.\n");
}

$force = in_array('--force', $argv);
$dryRun = in_array('--dry-run', $argv);

global $conn;

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No data will be deleted\n\n";
}

try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM Threads");
    $threadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM Comments");
    $commentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM Threads WHERE MediaPath IS NOT NULL AND MediaType != 'url'");
    $threadMediaCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM Comments WHERE MediaPath IS NOT NULL AND MediaType != 'url'");
    $commentMediaCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $totalMediaFiles = $threadMediaCount + $commentMediaCount;

    echo "Current Content Statistics:\n";
    echo "   - Threads: {$threadCount}\n";
    echo "   - Comments: {$commentCount}\n";
    echo "   - Uploaded media files: {$totalMediaFiles}\n\n";

    if ($threadCount === 0 && $commentCount === 0) {
        echo "No content to wipe. Database is already clean.\n";
        exit(0);
    }

    if (!$force && !$dryRun) {
        echo "WARNING: This will permanently delete ALL content!\n";
        echo "Type 'DELETE ALL' to confirm: ";

        $handle = fopen("php://stdin", "r");
        $confirmation = trim(fgets($handle));
        fclose($handle);

        if ($confirmation !== 'DELETE ALL') {
            echo "\nOperation cancelled.\n";
            exit(0);
        }
        echo "\n";
    }

    if ($dryRun) {
        echo "Would delete:\n";
        echo "  - {$threadCount} threads\n";
        echo "  - {$commentCount} comments\n";
        echo "  - {$totalMediaFiles} uploaded files\n\n";
        exit(0);
    }

    echo "Starting content wipe...\n\n";

    $conn->beginTransaction();

    echo "[1/4] Deleting uploaded media files...\n";
    $deletedFiles = 0;
    $failedFiles = 0;

    $stmt = $conn->query("SELECT MediaPath, MediaType FROM Threads WHERE MediaPath IS NOT NULL AND MediaType != 'url'");
    $threadMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("SELECT MediaPath, MediaType FROM Comments WHERE MediaPath IS NOT NULL AND MediaType != 'url'");
    $commentMedia = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allMedia = array_merge($threadMedia, $commentMedia);

    foreach ($allMedia as $media) {
        if (\pib\FileUpload::deleteFile($media['MediaPath'])) {
            $deletedFiles++;
        } else {
            $failedFiles++;
            echo "Failed to delete: {$media['MediaPath']}\n";
        }
    }

    echo "  ✓ Deleted {$deletedFiles} files";
    if ($failedFiles > 0) {
        echo " ({$failedFiles} failed)";
    }
    echo "\n\n";

    echo "[2/4] Deleting comments...\n";
    $stmt = $conn->prepare("DELETE FROM Comments");
    $stmt->execute();
    echo "  ✓ Deleted {$commentCount} comments\n\n";

    echo "[3/4] Deleting threads...\n";
    $stmt = $conn->prepare("DELETE FROM Threads");
    $stmt->execute();
    echo "  ✓ Deleted {$threadCount} threads\n\n";

    echo "[4/4] Resetting auto-increment counters...\n";
    $conn->exec("ALTER TABLE Threads AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE Comments AUTO_INCREMENT = 1");
    echo "  ✓ Counters reset\n\n";

    $conn->commit();

    echo "Summary:\n";
    echo "  - Threads deleted: {$threadCount}\n";
    echo "  - Comments deleted: {$commentCount}\n";
    echo "  - Files deleted: {$deletedFiles}\n";
    if ($failedFiles > 0) {
        echo "  - Failed deletions: {$failedFiles}\n";
    }
    echo "\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were made.\n";
    exit(1);
}
