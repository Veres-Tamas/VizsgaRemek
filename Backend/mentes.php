<?php
session_start();
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Frontend/galeria.html');
    exit;
}

$helyszin  = trim($_POST['helyszin'] ?? '');
$nev       = trim($_POST['nev'] ?? '');
$datum_tol = $_POST['datum_tol'] ?? '';
$datum_ig  = $_POST['datum_ig'] ?? '';
$vissza    = $_POST['vissza'] ?? '../Frontend/galeria.html';

// Csak a saját oldalainkra engedjük vissza-irányítani (nyitott redirect elleni védelem)
$engedelyezett_oldalak = ['bivak-cserepesko.php', 'bivak-toldi.php'];
if (!in_array($vissza, $engedelyezett_oldalak, true)) {
    $vissza = '../Frontend/galeria.html';
}

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
    $hibak[] = 'A kezdő dátum nem lehet később, mint a záró dátum.';
}

if (!empty($hibak)) {
    $_SESSION['flash'] = [
        'tipus' => 'hiba',
        'uzenet' => implode(' ', $hibak),
    ];
    header('Location: ' . $vissza);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO bejelentesek (helyszin, nev, datum_tol, datum_ig) VALUES (:helyszin, :nev, :datum_tol, :datum_ig)'
);
$stmt->execute([
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
