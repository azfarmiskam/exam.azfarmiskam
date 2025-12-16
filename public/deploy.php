<?php
/**
 * Deployment Helper Script - Exam System
 * Upload to public folder, access via browser, then DELETE
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adjust path based on location (assuming public/deploy.php)
$basePath = __DIR__ . '/..';
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$output = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $command = '';
            $params = [];
            $message = '';
            
            switch ($_POST['action']) {
                case 'migrate':
                    $command = 'migrate';
                    $params = ['--force' => true];
                    $message = 'Migrations run successfully!';
                    break;
                case 'seed':
                    $command = 'db:seed';
                    $params = ['--force' => true];
                    $message = 'Seeders run successfully!';
                    break;
                case 'cache':
                    $command = 'optimize:clear';
                    $message = 'Cache cleared successfully!';
                    break;
                case 'storage':
                    $command = 'storage:link';
                    $message = 'Storage linked successfully!';
                    break;
            }
            
            if ($command) {
                // Capture output
                $exitCode = $kernel->call($command, $params);
                $cmdOutput = $kernel->output();
                $output = $message . "\n\n" . $cmdOutput;
                $statusClass = 'success';
            }
        }
    } catch (Exception $e) {
        $output = 'Error: ' . $e->getMessage();
        $statusClass = 'error';
    }
}

// Check database connection for status display
$dbStatus = 'Unknown';
try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbStatus = 'Connected to: ' . Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    $dbClass = 'success';
} catch (Exception $e) {
    $dbStatus = 'Connection Failed: ' . $e->getMessage();
    $dbClass = 'error';
}

if (isset($_GET['delete']) && $_GET['delete'] == '1') {
    if (unlink(__FILE__)) {
        echo '<p style="color: green; font-weight: bold; text-align: center; font-family: sans-serif; margin-top: 50px;">✓ File deleted successfully!</p>';
        echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Exam System Deployment Helper</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f3f4f6; color: #1f2937; }
        .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        h1 { color: #111827; margin-top: 0; font-size: 1.875rem; }
        .status-bar { padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; font-size: 0.875rem; }
        .success { background-color: #ecfdf5; color: #047857; text-align: left;}
        .error { background-color: #fef2f2; color: #b91c1c; }
        .code-output { background: #1f2937; color: #f9fafb; padding: 20px; border-radius: 8px; margin: 24px 0; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; white-space: pre-wrap; font-size: 0.875rem; overflow-x: auto; }
        .actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .btn { display: inline-flex; justify-content: center; align-items: center; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 500; text-decoration: none; transition: background-color 0.2s; width: 100%; box-sizing: border-box; }
        .btn-primary { background-color: #2563eb; color: white; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-secondary { background-color: #4b5563; color: white; }
        .btn-secondary:hover { background-color: #374151; }
        .btn-green { background-color: #059669; color: white; }
        .btn-green:hover { background-color: #047857; }
        .btn-danger { background-color: #dc2626; color: white; }
        .btn-danger:hover { background-color: #b91c1c; }
        .footer-actions { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .warning { color: #b91c1c; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Deployment Helper</h1>
        
        <div class="status-bar <?php echo $dbClass; ?>">
             Database Status: <?php echo $dbStatus; ?>
        </div>
        
        <?php if ($output): ?>
            <div class="code-output"><?php echo htmlspecialchars($output); ?></div>
        @endif

        <h3>Available Actions</h3>
        <div class="actions">
            <form method="POST">
                <input type="hidden" name="action" value="migrate">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Run Migrations? This will update the database schema.')">📦 Run Migrations</button>
            </form>

            <form method="POST">
                <input type="hidden" name="action" value="seed">
                <button type="submit" class="btn btn-green" onclick="return confirm('Run Seeders? This will insert test data.')">🌱 Run Seeders</button>
            </form>

            <form method="POST">
                <input type="hidden" name="action" value="cache">
                <button type="submit" class="btn btn-secondary">🧹 Clear Cache</button>
            </form>
            
            <form method="POST">
                <input type="hidden" name="action" value="storage">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Attempt to link storage?')">🔗 Link Storage</button>
            </form>
        </div>
        
        <div class="footer-actions">
            <div class="warning">
                ⚠️ <strong>Security Warning:</strong> Delete this file immediately after use.
            </div>
            <a href="?delete=1" class="btn btn-danger" style="width: auto;" onclick="return confirm('Are you sure you want to delete this file? You wont be able to use it again without re-uploading.')">❌ Delete Helper File</a>
        </div>
    </div>
</body>
</html>
