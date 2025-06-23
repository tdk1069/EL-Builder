<?php
// db.php
$host = 'mariadb';  // or 'localhost'
$db   = 'mud';
$user = 'root';
$pass = 'S!cr9t';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Step 1: Connect without database to ensure mud exists
$dsnNoDb = "mysql:host=$host;charset=$charset";
$pdo = new PDO($dsnNoDb, $user, $pass, $options);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET $charset");

// Step 2: Connect to mud database
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, $options);

// Step 3: Ensure all required tables exist
$tables = [

    "users" => "
        CREATE TABLE IF NOT EXISTS users (
            id INT(11) NOT NULL AUTO_INCREMENT,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        )
    ",

    "areas" => "
        CREATE TABLE IF NOT EXISTS areas (
            id INT(11) NOT NULL AUTO_INCREMENT,
            username VARCHAR(255) NOT NULL,
            name VARCHAR(255),
            basePath VARCHAR(255),
            levelRange VARCHAR(255),
            description TEXT,
            PRIMARY KEY (id)
        )
    ",

    "rooms" => "
        CREATE TABLE IF NOT EXISTS rooms (
            id INT(11) NOT NULL AUTO_INCREMENT,
            area_id INT(11) NOT NULL,
            username VARCHAR(255) NOT NULL,
            name VARCHAR(255),
            basePath VARCHAR(255),
            levelRange VARCHAR(255),
            description TEXT,
            data LONGTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY (area_id)
        )
    ",

    "monsters" => "
        CREATE TABLE IF NOT EXISTS monsters (
            id INT(11) NOT NULL AUTO_INCREMENT,
            owner VARCHAR(255) NOT NULL,
            set_short TEXT,
            set_name TEXT NOT NULL,
            set_long TEXT,
            set_spells TEXT,
            set_class TEXT NOT NULL,
            set_race TEXT NOT NULL,
            set_level INT(11) NOT NULL,
            set_gender TEXT NOT NULL,
            PRIMARY KEY (id)
        )
    ",

    "obj" => "
        CREATE TABLE IF NOT EXISTS obj (
            id INT(11) NOT NULL AUTO_INCREMENT,
            owner VARCHAR(255) NOT NULL,
            class VARCHAR(255) NOT NULL,
            short TEXT,
            name TEXT,
            longdesc TEXT,
            level INT(11),
            PRIMARY KEY (id)
        )
    ",

    "monsterObj" => "
        CREATE TABLE IF NOT EXISTS monsterObj (
            id INT(11) NOT NULL AUTO_INCREMENT,
            monster_id INT(11) NOT NULL,
            object_id INT(11) NOT NULL,
            PRIMARY KEY (id)
        )
    "
];

foreach ($tables as $name => $sql) {
    $pdo->exec($sql);
}

// Step 4: Return connected PDO
return $pdo;
