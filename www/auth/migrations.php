<?php
// migrations.php
require_once 'db.php'; // Ensure this gets the $mysqli connection

function tableExists($mysqli, $tableName) {
    $result = $mysqli->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

function runMigrations($mysqli) {
    // Users table
    if (!tableExists($mysqli, 'users')) {
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $mysqli->query($sql);
    }

    // Areas table
    if (!tableExists($mysqli, 'areas')) {
        $sql = "CREATE TABLE areas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            basePath VARCHAR(255) NOT NULL,
            levelRange VARCHAR(50),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $mysqli->query($sql);
    }

    // Add future migrations here
}

