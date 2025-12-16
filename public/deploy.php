<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Deployment Debugger</h1>";

// 1. Check PHP Version
echo "<h2>1. PHP Version</h2>";
echo "Current Version: " . phpversion() . "<br>";
echo "Required: >= 8.2 (for Laravel 11/12)<br>";

if (version_compare(phpversion(), '8.2.0', '<')) {
    echo "<div style='color:red'>❌ CRITICAL: Your PHP version is too old. You need to upgrade to PHP 8.2 or higher in your hosting control panel.</div>";
} else {
    echo "<div style='color:green'>✅ PHP version is OK.</div>";
}

// 2. Check Paths
echo "<h2>2. File System Checks</h2>";
$basePath = dirname(__DIR__);
$vendorPath = $basePath . '/vendor/autoload.php';
$bootstrapPath = $basePath . '/bootstrap/app.php';

echo "Root Path: " . $basePath . "<br>";
echo "Vendor Path: " . $vendorPath . " ... " . (file_exists($vendorPath) ? "✅ Found" : "❌ NOT FOUND") . "<br>";
echo "Bootstrap Path: " . $bootstrapPath . " ... " . (file_exists($bootstrapPath) ? "✅ Found" : "❌ NOT FOUND") . "<br>";

// 3. Permissions Check
echo "<h2>3. Permissions Check</h2>";
$storagePath = $basePath . '/storage';
$cachePath = $basePath . '/bootstrap/cache';

$isStorageWritable = is_writable($storagePath);
echo "Storage Writable: " . ($isStorageWritable ? "✅ Yes" : "❌ NO") . "<br>";

$isCacheWritable = is_writable($cachePath);
echo "Bootstrap Cache Writable: " . ($isCacheWritable ? "✅ Yes" : "❌ NO") . "<br>";

// 4. Load Application
echo "<h2>4. Application Boot</h2>";
try {
    if (!file_exists($vendorPath)) {
        throw new Exception("Vendor autoload not found. Did you upload the 'vendor' folder?");
    }
    
    echo "Requires Loading...<br>";
    require $vendorPath;
    echo "Vendor Loaded. Bootstrapping App...<br>";
    
    $app = require_once $bootstrapPath;
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "<div style='color:green'>✅ Application Booted Successfully!</div>";
    
    // If we got here, we can show the original UI
    echo "<hr><h2>Deployment Tools</h2>";
    echo '<form method="POST">
            <input type="hidden" name="action" value="migrate">
            <button type="submit">Run Migrations</button>
          </form>';
          
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'migrate') {
        echo "<h3>Running Migrations...</h3>";
        $kernel->call('migrate', ['--force' => true]);
        echo "<pre>" . $kernel->output() . "</pre>";
    }

} catch (Throwable $e) {
    echo "<div style='color:red'>";
    echo "<h3>❌ Application Crashed</h3>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
