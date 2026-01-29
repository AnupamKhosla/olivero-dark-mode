<?php
/**
 * @file
 * settings.upsun.php - TRACE DEBUG VERSION
 */

// CHECKPOINT 1: START
echo "<h1>[DEBUG] 1. settings.upsun.php STARTING...</h1>";
flush();

try {
    $platformsh = new \Platformsh\ConfigReader\Config();
    echo "<p>[DEBUG] Config object created successfully.</p>";
} catch (\Exception $e) {
    die("<h1>[FATAL] Failed to create Config object: " . $e->getMessage() . "</h1>");
}

// CHECKPOINT 2: CLASS LOADER
if (isset($class_loader)) {
    echo "<p>[DEBUG] 2. \$class_loader is present.</p>";
    
    // We use /app/web explicitly to avoid any ambiguity
    $class_loader->addPsr4('Drupal\\mysql\\', '/app/web/core/modules/mysql/src');
    
    echo "<p>[DEBUG] ... PSR-4 Map added for 'Drupal\mysql'.</p>";
} else {
    // We don't die here, just report it, because maybe it's not fatal yet?
    echo "<h2 style='color:red'>[WARNING] \$class_loader is MISSING! The map fix cannot work.</h2>";
}

// CHECKPOINT 3: DATABASE
if ($platformsh->hasRelationship('mariadb')) {
    echo "<p>[DEBUG] 3. 'mariadb' relationship found.</p>";
    
    $creds = $platformsh->credentials('mariadb');
    echo "<p>[DEBUG] ... Credentials retrieved (Host: " . $creds['host'] . ")</p>";
    
    $databases['default']['default'] = [
        'driver' => 'mysql',
        // --- THE CRITICAL LINE ---
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
    echo "<p>[DEBUG] ... Database array populated with 'namespace' key.</p>";
} else {
    die('<h1>[FATAL] Relationship "mariadb" not found in Config.</h1>');
}

// CHECKPOINT 4: VERIFICATION
// This is the moment of truth. Can PHP actually see the class now?
if (class_exists('Drupal\mysql\Driver\Database\mysql\Connection')) {
    echo "<h1 style='color:green'>[DEBUG] 4. SUCCESS: MySQL Driver Class Found!</h1>";
} else {
    echo "<h1 style='color:red'>[DEBUG] 4. FAILURE: MySQL Driver Class NOT Found.</h1>";
    echo "<p>Path checked: /app/web/core/modules/mysql/src</p>";
    // We let it continue to see if Drupal throws a specific error
}

// CHECKPOINT 5: RUNTIME
if ($platformsh->inRuntime()) {
    echo "<p>[DEBUG] 5. Applying Runtime settings...</p>";
    $settings['hash_salt'] = $platformsh->projectEntropy;
    $settings['deployment_identifier'] = $platformsh->treeId;
    $settings['trusted_host_patterns'] = ['.*'];
    $settings['config_sync_directory'] = '../config/sync';
}

echo "<h1>[DEBUG] 6. End of settings.upsun.php (Handing back to Drupal)</h1><hr>";
flush();