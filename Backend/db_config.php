<?php
// Töltsd ki a saját adatbázis-eléréseddel
$host = 'localhost';
$dbname = 'fotogaleria';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Adatbázis kapcsolódási hiba. Ellenőrizd a db_config.php beállításait.');
}
