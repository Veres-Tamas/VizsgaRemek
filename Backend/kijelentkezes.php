<?php
// Kijelentkezés: egyszerűen töröljük a munkamenet teljes tartalmát, majd
// visszairányítunk a galériára egy "sikeresen kijelentkeztél" üzenettel.
require 'auth.php';

// A $_SESSION tömb kiürítése és a munkamenet lezárása a szerver oldalon.
$_SESSION = [];
session_destroy();

// Új, üres munkamenetet nyitunk, hogy bele tudjuk tenni a visszajelző
// (flash) üzenetet, amit a galéria oldal egyszer megjelenít, majd eldob.
session_start();
$_SESSION['flash'] = [
    'tipus'  => 'siker',
    'uzenet' => 'Sikeresen kijelentkeztél.',
];
header('Location: ../Frontend/galeria.php');
exit;
