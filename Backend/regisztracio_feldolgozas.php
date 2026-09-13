<?php
// A regisztracio.php form POST-olja ide az adatokat. Ez a fájl:
//   1) leellenőrzi az adatokat (validáció),
//   2) ha minden rendben, elmenti az új felhasználót az adatbázisba,
//   3) automatikusan be is jelentkezteti,
//   4) végül visszairányít a galériára egy visszajelző (flash) üzenettel.
require 'auth.php';
require 'db_config.php';

// Ha valaki közvetlenül (POST nélkül, pl. a böngésző címsorába beírva) próbálja
// megnyitni ezt a fájlt, nincs mit feldolgozni - irányítsuk vissza az űrlapra.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: regisztracio.php');
    exit;
}

// Beérkezett form-adatok kiolvasása. A "?? ''" biztosítja, hogy hiányzó mező
// esetén ne PHP hibát kapjunk, hanem üres string kerüljön a változóba.
$nev                = trim($_POST['nev'] ?? '');
$email              = trim($_POST['email'] ?? '');
$jelszo             = $_POST['jelszo'] ?? '';
$jelszo_megerosites = $_POST['jelszo_megerosites'] ?? '';

$hibak = [];

// --- Validáció: minden hibát összegyűjtünk, hogy egyszerre lássa a felhasználó ---
if ($nev === '' || mb_strlen($nev) > 100) {
    $hibak[] = 'Add meg a neved (max. 100 karakter)!';
}
if ($email === '' || mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $hibak[] = 'Adj meg egy érvényes e-mail címet!';
}
if (mb_strlen($jelszo) < 6) {
    $hibak[] = 'A jelszónak legalább 6 karakter hosszúnak kell lennie!';
}
if ($jelszo !== $jelszo_megerosites) {
    $hibak[] = 'A két jelszó nem egyezik!';
}

// Csak akkor érdemes az adatbázist is lekérdezni az e-mail egyediségéhez,
// ha eddig minden más mező helyes volt (nincs értelme felesleges lekérdezésnek).
if (empty($hibak)) {
    $stmt = $pdo->prepare('SELECT id FROM felhasznalok WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $hibak[] = 'Ezzel az e-mail címmel már regisztráltak.';
    }
}

// Ha volt bármilyen hiba, nem mentünk semmit: visszaküldjük a hibaüzeneteket
// a form oldalára (session flash-en keresztül), és megállunk itt.
if (!empty($hibak)) {
    $_SESSION['flash'] = [
        'tipus'  => 'hiba',
        'uzenet' => implode(' ', $hibak),
    ];
    header('Location: regisztracio.php');
    exit;
}

// A jelszót SOHA nem tároljuk nyílt szövegként - a password_hash() egy
// visszafejthetetlen, "sózott" hash-t készít belőle. Belépéskor a
// password_verify() függvény tudja majd ellenőrizni, hogy egyezik-e egy
// beírt jelszó ezzel a hash-sel, magát a jelszót nem kell hozzá ismerni.
$jelszo_hash = password_hash($jelszo, PASSWORD_DEFAULT);

// Új sor beszúrása a felhasznalok táblába. Előkészített lekérdezést (prepared
// statement) használunk a :placeholder-ekkel, hogy SQL injection ne
// fordulhasson elő még akkor sem, ha valaki idézőjeleket ír a mezőkbe.
$stmt = $pdo->prepare(
    'INSERT INTO felhasznalok (nev, email, jelszo_hash) VALUES (:nev, :email, :jelszo_hash)'
);
$stmt->execute([
    'nev'         => $nev,
    'email'       => $email,
    'jelszo_hash' => $jelszo_hash,
]);

// Sikeres regisztráció után rögtön be is jelentkeztetjük a felhasználót:
// beírjuk a munkamenetbe az imént létrehozott sor azonosítóját és a nevét.
// A lastInsertId() az utoljára beszúrt sor auto-increment id-ját adja vissza.
$_SESSION['user_id']  = (int) $pdo->lastInsertId();
$_SESSION['user_nev'] = $nev;

$_SESSION['flash'] = [
    'tipus'  => 'siker',
    'uzenet' => 'Sikeres regisztráció! Be vagy jelentkezve.',
];
header('Location: ../Frontend/galeria.php');
exit;
