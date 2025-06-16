<?php
// db.php
$host = 'mariadb';  // or 'localhost'
$db   = 'mud';
$user = 'root';
$pass = 'S!cr9t';
$charset = 'utf8mb4';

// Connect without specifying DB first (to create if needed)
$dsnNoDb = "mysql:host=$host;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsnNoDb, $user, $pass, $options);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // Connect again, this time to the database
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Create users table if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    // Create areas table if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS areas (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            name VARCHAR(255) DEFAULT NULL,
            basePath VARCHAR(255) DEFAULT NULL,
            levelRange VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS obj (
			id INT AUTO_INCREMENT PRIMARY KEY,
			owner VARCHAR(255) NOT NULL,
			class VARCHAR(255) NOT NULL,
			short TEXT,
			longdesc TEXT,
                        level INT
		)
	");

    // Create monsters table if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS monsters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner VARCHAR(255) NOT NULL,
            set_short TEXT,
            set_long TEXT,
            set_class TEXT NOT NULL,
            set_race TEXT NOT NULL,
            set_level INT NOT NULL,
            set_gender TEXT NOT NULL
        )
    ");

    // Create MonsterRooms table if missing
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS MonsterRooms (
            monster_id INT NOT NULL,
            room_id INT NOT NULL,
            area_id INT NOT NULL,
            PRIMARY KEY (monster_id, room_id),
            FOREIGN KEY (monster_id) REFERENCES monsters(id) ON DELETE CASCADE,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
        );
   ");

// --- Schema builder: ensure table and column exist ---
try {
    // Check if 'rooms' table exists
    $result = $pdo->query("SHOW TABLES LIKE 'rooms'");
    if ($result->rowCount() === 0) {
        // Create table if missing
$pdo->exec("
    CREATE TABLE IF NOT EXISTS rooms (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        area_id INT NOT NULL,
        username VARCHAR(255) NOT NULL,
        name VARCHAR(255) DEFAULT NULL,
        basePath VARCHAR(255) DEFAULT NULL,
        levelRange VARCHAR(255) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        data LONGTEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (area_id),
        FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");
    } else {
        // Table exists - check if 'data' column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM rooms LIKE 'data'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            // Add 'data' column if missing
            $pdo->exec("ALTER TABLE rooms ADD COLUMN data LONGTEXT DEFAULT NULL");
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB schema error: ' . $e->getMessage()]);
    exit;
}

} catch (\PDOException $e) {
    // Handle connection error gracefully
    die("Database error: " . $e->getMessage());
}

return $pdo;
