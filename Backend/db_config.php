<?php
// Adatbázis-kapcsolódási adatok. XAMPP alapértelmezett beállításokkal:
// helyi MySQL szerver, "root" felhasználó, jelszó nélkül.
// Éles környezetben ezeket soha nem tennénk verziókezelőbe / nyilvánosan
// elérhető helyre - itt vizsgaremek lévén, egyszerűség kedvéért itt van.
$host   = 'localhost';
$dbname = 'fotogaleria';
$user   = 'root';
$pass   = '';

// PDO (PHP Data Objects): egységes, adatbázis-független kapcsolódási réteg.
// Ezt az objektumot ($pdo) minden más .php fájl felhasználja, miután
// require-elte ezt a fájlt.
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            // Hiba esetén dobjon kivételt (Exception), ne csak return false-t adjon -
            // így egyszerűbb hibát kezelni / észrevenni, ha valami elromlik.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Lekérdezés eredménye alapból asszociatív tömb legyen (pl. $sor['nev']),
            // ne számindexelt tömb és ne objektum.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Ha nem sikerül kapcsolódni (pl. nem fut a MySQL, vagy rossz jelszó),
    // ne a nyers hibaüzenetet mutassuk a látogatónak, hanem egy érthető szöveget.
    die('Adatbázis kapcsolódási hiba. Ellenőrizd a db_config.php beállításait.');
}
