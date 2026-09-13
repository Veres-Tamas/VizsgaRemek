<?php
// Ez a fájl dolgozza fel a bivak-cserepesko.php / bivak-toldi.php oldalak
// "Tervezett időpont jelzése" form-jának beküldését: leellenőrzi az adatokat,
// és ha rendben vannak, új sort szúr be a bejelentesek táblába.
require 'auth.php';
require 'db_config.php';
// Ha az adatbázist időközben törölték/újratöltötték, egy régi munkamenet
// érvénytelen user_id-t tartalmazhatna - ez itt megelőzi az idegenkulcs-hibát.
session_ervenyesitese($pdo);

// Közvetlen (nem form-ból érkező) hívás esetén nincs mit feldolgozni.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Frontend/galeria.php');
    exit;
}

$helyszin  = trim($_POST['helyszin'] ?? '');
$datum_tol = $_POST['datum_tol'] ?? '';
$datum_ig  = $_POST['datum_ig'] ?? '';
// A "vissza" mezőt a form rejtett inputként küldi, hogy tudjuk, melyik
// bivak-oldalra kell visszairányítani a felhasználót mentés után.
$vissza    = $_POST['vissza'] ?? '../Frontend/galeria.php';

// Bejelentkezett felhasználónál a fiók neve számít - így a bejelentés
// biztosan az ő fiókjához köthető, és utólag ő tudja majd szerkeszteni.
// (A form is le van tiltva/name nélküli input logikailag ilyenkor, tehát
// nem is küld nev mezőt - de a szerver oldali ellenőrzés a biztonság kedvéért
// ettől függetlenül mindig a fiók nevét használja, nem bízik a beküldött adatban.)
$nev = bejelentkezve() ? aktualis_user_nev() : trim($_POST['nev'] ?? '');

// Csak a saját oldalainkra engedjük vissza-irányítani (nyitott redirect elleni védelem):
// ha valaki kézzel módosítaná a "vissza" mező értékét egy külső URL-re, ezzel
// megakadályozzuk, hogy oda irányítsuk a felhasználót.
$engedelyezett_oldalak = ['bivak-cserepesko.php', 'bivak-toldi.php'];
if (!in_array($vissza, $engedelyezett_oldalak, true)) {
    $vissza = '../Frontend/galeria.php';
}

// --- Validáció: minden hibát összegyűjtünk, hogy egyszerre lássa a felhasználó ---
$hibak = [];

if ($helyszin === '') {
    $hibak[] = 'Hiányzó helyszín.';
}
if ($nev === '' || mb_strlen($nev) > 100) {
    $hibak[] = 'Add meg a neved (max. 100 karakter)!';
}
if (!$datum_tol || !$datum_ig || !DateTime::createFromFormat('Y-m-d', $datum_tol) || !DateTime::createFromFormat('Y-m-d', $datum_ig)) {
    $hibak[] = 'Add meg mindkét dátumot érvényes formában!';
} elseif ($datum_tol > $datum_ig) {
    // A dátumok "ÉÉÉÉ-HH-NN" formátumban vannak, ami szövegként összehasonlítva
    // is helyesen rendeződik időrendben, tehát a > operátor itt működik.
    $hibak[] = 'A kezdő dátum nem lehet később, mint a záró dátum.';
}

// Ha volt hiba, nem mentünk semmit: visszaküldjük a hibaüzenetet a beküldő
// oldalra (session flash-en keresztül), és megállunk itt.
if (!empty($hibak)) {
    $_SESSION['flash'] = [
        'tipus' => 'hiba',
        'uzenet' => implode(' ', $hibak),
    ];
    header('Location: ' . $vissza);
    exit;
}

// Új bejelentés beszúrása. Ha nincs bejelentkezve senki, az aktualis_user_id()
// null-t ad vissza, ami a user_id oszlopba NULL-ként kerül be (a mező ezt
// megengedi) - ez egy "gazdátlan", utólag nem szerkeszthető bejelentés lesz.
$stmt = $pdo->prepare(
    'INSERT INTO bejelentesek (user_id, helyszin, nev, datum_tol, datum_ig)
     VALUES (:user_id, :helyszin, :nev, :datum_tol, :datum_ig)'
);
$stmt->execute([
    'user_id'   => aktualis_user_id(),
    'helyszin'  => $helyszin,
    'nev'       => $nev,
    'datum_tol' => $datum_tol,
    'datum_ig'  => $datum_ig,
]);

$_SESSION['flash'] = [
    'tipus' => 'siker',
    'uzenet' => 'Köszönjük, a tervezett időpontot rögzítettük!',
];
header('Location: ' . $vissza);
exit;
