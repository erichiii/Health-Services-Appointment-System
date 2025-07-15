<?php

$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'health_services',
    'username' => 'u250652218_admin',
    'password' => 'Healthservices123',
    'database' => 'u250652218_health_service',
    'charset' => 'utf8mb4'
];


// Create connection
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Optional: Display success message (remove in production)
    // echo "Database connection successful!<br>";

} catch (PDOException $e) {
    // In production, log the error instead of displaying it
    die("Connection failed: " . $e->getMessage());
}

// Function to get database connection (optional, for consistency)
function getDbConnection()
{
    global $pdo;
    return $pdo;
}
