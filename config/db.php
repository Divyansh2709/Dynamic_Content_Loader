<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function ensureSchema(mysqli $conn): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $conn->query('CREATE DATABASE IF NOT EXISTS demo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $conn->select_db('demo_db');

    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        author VARCHAR(100) NOT NULL DEFAULT 'Anonymous',
        category VARCHAR(50) NOT NULL DEFAULT 'General',
        user_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_posts_user_id (user_id),
        CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Migrate older installations where posts table predates auth ownership fields.
    $userIdColumn = $conn->query("SHOW COLUMNS FROM posts LIKE 'user_id'");
    if ($userIdColumn && $userIdColumn->num_rows === 0) {
        $conn->query('ALTER TABLE posts ADD COLUMN user_id INT NULL');
    }

    $userIdIndex = $conn->query("SHOW INDEX FROM posts WHERE Key_name = 'idx_posts_user_id'");
    if ($userIdIndex && $userIdIndex->num_rows === 0) {
        $conn->query('CREATE INDEX idx_posts_user_id ON posts (user_id)');
    }

    $fk = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = 'demo_db' AND TABLE_NAME = 'posts' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = 'fk_posts_user'");
    if ($fk && $fk->num_rows === 0) {
        $conn->query('ALTER TABLE posts ADD CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    $initialized = true;
}

function getDbConnection(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    $conn = new mysqli('localhost', 'root', '');
    $conn->set_charset('utf8mb4');
    ensureSchema($conn);

    return $conn;
}
