<?php
/**
 * @file
 * settings.upsun.php - DEBUG VERSION
 */

$platformsh = new \Platformsh\ConfigReader\Config();

// 1. VERIFY FILE LOADED
// If you don't see this at the top of your page, this file isn't being included!
print "";

// 2. APPLY MAP FIX (Hardcoded Path)
if (isset($class_loader)) {
    // We use /app/web explicitly to avoid any ambiguity
    $class_loader->addPsr4('Drupal\\mysql\\', '/app/web/core/modules/mysql/src');
} else {
    die('<h1>CRITICAL ERROR: $class_loader is missing in settings.php</h1>');
}

// 3. CONFIGURE DATABASE
if ($platformsh->hasRelationship('mariadb')) {
    $creds = $platformsh->credentials('mariadb');
    
    $databases['default']['default'] = [
        'driver' => 'mysql',
        // --- THE CRITICAL LINE ---
        // Without this, Drupal ignores the map we just added!
        'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql', 
        // -------------------------
        'database' => $creds['path'],
        'username' => $creds['username'],
        'password' => $creds['password'],
        'host' => $creds['host'],
        'port' => $creds['port'],
        'pdo' => [PDO::MYSQL_ATTR_COMPRESS => !empty($creds['query']['compression'])],
        'init_commands' => [
            'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
        ],
    ];
} else {
    // STOP EVERYTHING if we can't find the database key
    die('<h1>CRITICAL ERROR: Relationship "mariadb" not found in Config.</h1>');
}

// 4. VERIFY CLASS VISIBILITY
// This will tell us if the fix actually worked in the web context
if (!class_exists('Drupal\mysql\Driver\Database\mysql\Connection')) {
    die('<h1>CRITICAL ERROR: MySQL Driver Class is STILL NOT FOUND.</h1><p>The addPsr4 fix failed.</p>');
}

// 5. RUNTIME SETTINGS
if ($platformsh->inRuntime()) {
    $settings['hash_salt'] = $platformsh->projectEntropy;
    $settings['deployment_identifier'] = $platformsh->treeId;
    $settings['trusted_host_patterns'] = ['.*'];
    $settings['config_sync_directory'] = '../config/sync';
}