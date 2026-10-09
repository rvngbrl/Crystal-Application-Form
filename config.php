<?php
declare(strict_types=1);

/**
 * Crystal Shipping Inc. - Application Portal Configuration & Remote Database
 * Connects directly to the Crystal Shipping database independently of localhost.
 */

// ---------------------------------------------------------------------------
// Environment Variable Loader (.env)
// ---------------------------------------------------------------------------
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// ---------------------------------------------------------------------------
// Database Configuration
// ---------------------------------------------------------------------------
if (!defined('COMPANY_NAME')) {
    define('COMPANY_NAME', 'Crystal Shipping Inc.');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '192.185.23.171');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'crystinc_ojt');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'Cry$talOJT2026');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'crystinc_crystaldb');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
}

// Folder for uploaded photos & application attachments
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/uploads/');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (DB_HOST === '' || DB_USER === '' || DB_NAME === '') {
    http_response_code(500);
    die('Database configuration is incomplete. Please set DB_HOST, DB_USER, DB_PASS, and DB_NAME.');
}

try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $db->set_charset('utf8mb4');
    $dbDebugMessage = 'Database connection successful.';
    error_log($dbDebugMessage . ' Host: ' . DB_HOST . ', Database: ' . DB_NAME);
    if (PHP_SAPI === 'cli') {
        echo $dbDebugMessage . ' Host: ' . DB_HOST . ', Database: ' . DB_NAME . PHP_EOL;
    }
} catch (mysqli_sql_exception $e) {
    error_log('Database connection failed: ' . $e->getMessage() . ' [Host: ' . DB_HOST . ']');
    if (PHP_SAPI === 'cli') {
        echo 'Database connection failed: ' . $e->getMessage() . PHP_EOL;
    }
    http_response_code(500);
    die('Sorry, we could not connect to the database right now. Please try again later.');
}

/**
 * PDO Connection instance helper (optional, for scripts preferring PDO)
 */
function getPdo(): PDO
{
    static $pdoInstance = null;
    if ($pdoInstance instanceof PDO) {
        return $pdoInstance;
    }
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdoInstance = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdoInstance;
}
