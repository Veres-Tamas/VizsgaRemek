<?php
// A belepes.php form POST-olja ide az e-mail címet és a jelszót. Ez a fájl
// ellenőrzi, hogy létezik-e ilyen felhasználó és stimmel-e a jelszava, majd
// sikeres belépés esetén beírja a munkamenetbe az azonosítóját.
require 'auth.php';
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: belepes.php');
    exit;
}

$email  = trim($_POST['email'] ?? '');
$jelszo = $_POST['jelszo'] ?? '';

$hiba        = null;
$felhasznalo = null;

if ($email === '' || $jelszo === '') {
    $hiba = 'Add meg az e-mail címed és a jelszavad!';
} else {
    // Megkeressük a felhasználót e-mail cím alapján. Az e-mail a
    // felhasznalok táblában UNIQUE, tehát legfeljebb egy sort adhat vissza.
    $stmt = $pdo->prepare('SELECT id, nev, jelszo_hash FROM felhasznalok WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $felhasznalo = $stmt->fetch();

    // password_verify(): összeveti a beírt jelszót a regisztrációkor
    // elmentett hash-sel, anélkül hogy magát az eredeti jelszót ismernénk.
    // Szándékosan ugyanazt az általános hibaüzenetet adjuk vissza mindkét
    // esetben (nincs ilyen felhasználó / rossz a jelszó), hogy ne lehessen
    // ebből kitalálni, mely e-mail címekkel regisztráltak már.
    if (!$felhasznalo || !password_verify($jelszo, $felhasznalo['jelszo_hash'])) {
        $hiba = 'Hibás e-mail cím vagy jelszó.';
    }
}

if ($hiba) {
    $_SESSION['flash'] = [
        'tipus'  => 'hiba',
        'uzenet' => $hiba,
    ];
    header('Location: belepes.php');
    exit;
}

// Sikeres belépés: a felhasználó azonosítóját és nevét beírjuk a
// munkamenetbe - innentől bejelentkezve() igazat ad vissza rá.
$_SESSION['user_id']  = (int) $felhasznalo['id'];
$_SESSION['user_nev'] = $felhasznalo['nev'];

$_SESSION['flash'] = [
    'tipus'  => 'siker',
    'uzenet' => 'Sikeres belépés!',
];
header('Location: ../Frontend/galeria.php');
exit;
