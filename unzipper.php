<?php
/**
 * A utility to unzip `.zip`, `.rar`, and `.gz` archives, and create `.zip` archives.
 * @author ₦£L$0₦
 * @version 1.0
 */

define('VERSION', '1.0');
$executionTimeStart = microtime(true);
$status = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unzipper = new UnzipUtility();

    if (isset($_POST['dounzip'])) {
        $archive = $_POST['zipfile'] ?? '';
        $destination = $_POST['extpath'] ?? '';
        $status = $unzipper->extractArchive($archive, $destination);
    }

    if (isset($_POST['dozip'])) {
        $sourcePath = $_POST['zippath'] ?? '.';
        $zipFileName = 'archive-' . date('Y-m-d-H-i') . '.zip';
        $status = ZipUtility::createZipArchive($sourcePath, $zipFileName);
    }
}

$executionTimeEnd = microtime(true);
$executionTime = round($executionTimeEnd - $executionTimeStart, 4);

/**
 * Class to handle archive extraction.
 */
class UnzipUtility
{
    private $allowedExtensions = ['zip', 'gz', 'rar'];
    private $currentDirectory = '.';

    public function __construct()
    {
        // Check for supported archive types in the current directory
        $this->scanArchives();
    }

    private function scanArchives()
    {
        $this->archiveFiles = array_filter(scandir($this->currentDirectory), function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), $this->allowedExtensions);
        });
    }

    public function extractArchive(string $archive, string $destination = ''): array
    {
        if (!in_array($archive, $this->archiveFiles)) {
            return ['error' => "Archive file '$archive' not found."];
        }

        $destination = $destination ?: $this->currentDirectory;

        if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
            return ['error' => "Failed to create destination directory: $destination"];
        }

        $extension = pathinfo($archive, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'zip':
                return $this->extractZip($archive, $destination);
            case 'gz':
                return $this->extractGzip($archive, $destination);
            case 'rar':
                return $this->extractRar($archive, $destination);
            default:
                return ['error' => "Unsupported archive format: $extension"];
        }
    }

    private function extractZip(string $archive, string $destination): array
    {
        if (!class_exists('ZipArchive')) {
            return ['error' => 'PHP ZipArchive extension is not installed.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($archive) === true) {
            if ($zip->extractTo($destination)) {
                $zip->close();
                return ['success' => "Archive '$archive' extracted to '$destination'."];
            }
            $zip->close();
            return ['error' => "Failed to extract archive: $archive"];
        }

        return ['error' => "Unable to open zip archive: $archive"];
    }

    private function extractGzip(string $archive, string $destination): array
    {
        if (!function_exists('gzopen')) {
            return ['error' => 'PHP zlib extension is not enabled.'];
        }

        $outputFile = $destination . '/' . pathinfo($archive, PATHINFO_FILENAME);
        $inputFile = gzopen($archive, 'rb');
        $outputHandle = fopen($outputFile, 'wb');

        while (!gzeof($inputFile)) {
            fwrite($outputHandle, gzread($inputFile, 4096));
        }

        gzclose($inputFile);
        fclose($outputHandle);

        return ['success' => "Gzip file '$archive' extracted to '$outputFile'."];
    }

    private function extractRar(string $archive, string $destination): array
    {
        if (!class_exists('RarArchive')) {
            return ['error' => 'PHP RarArchive extension is not installed.'];
        }

        $rar = RarArchive::open($archive);
        if (!$rar) {
            return ['error' => "Unable to open rar archive: $archive"];
        }

        $entries = $rar->getEntries();
        foreach ($entries as $entry) {
            $entry->extract($destination);
        }
        $rar->close();

        return ['success' => "Rar archive '$archive' extracted to '$destination'."];
    }
}

/**
 * Class to handle zip creation.
 */
class ZipUtility
{
    public static function createZipArchive(string $sourcePath, string $outputFile): array
    {
        if (!class_exists('ZipArchive')) {
            return ['error' => 'PHP ZipArchive extension is not installed.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($outputFile, ZipArchive::CREATE) !== true) {
            return ['error' => "Failed to create zip archive: $outputFile"];
        }

        $sourcePath = realpath($sourcePath);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourcePath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        return ['success' => "Zip archive '$outputFile' created successfully."];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Utility</title>
</head>
<body>
    <h1>Archive Utility</h1>
    <form method="POST">
        <fieldset>
            <legend>Unzip Files</legend>
            <label for="zipfile">Select Archive:</label>
            <select name="zipfile" id="zipfile">
                <?php foreach ((new UnzipUtility())->archiveFiles as $file): ?>
                    <option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="extpath">Extraction Path:</label>
            <input type="text" name="extpath" id="extpath">
            <button type="submit" name="dounzip">Extract</button>
        </fieldset>

        <fieldset>
            <legend>Create Zip</legend>
            <label for="zippath">Folder to Zip:</label>
            <input type="text" name="zippath" id="zippath">
            <button type="submit" name="dozip">Create Zip</button>
        </fieldset>
    </form>
    <p>Status: <?= $status['success'] ?? $status['error'] ?? 'Ready' ?></p>
    <p>Execution Time: <?= $executionTime ?> seconds</p>
</body>
</html>
