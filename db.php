<?php
declare(strict_types=1);

/**
 * Database Connection - Crystal Shipping Inc. Application Portal
 * Provides $db (mysqli) and $pdo (PDO) connected directly to the remote database.
 */
require_once __DIR__ . '/config.php';

$pdo = getPdo();
