<?php
// Közös munkamenet- és bejelentkezés-kezelő segédfájl.
// Minden olyan PHP oldal, amelyiknek tudnia kell, hogy be van-e jelentkezve
// a látogató, ezt tölti be session_start() helyett.
//
// A munkamenet (session) PHP-oldalon úgy működik, hogy a látogató böngészője
// kap egy sütit (cookie) egy azonosítóval, a szerver pedig ehhez az
// azonosítóhoz tárolja a $_SESSION tömb tartalmát a saját oldalán. Így tudjuk
// oldalak között (pl. bejelentkezés -> galéria -> kijelentkezés) megjegyezni,
// hogy ki van bejelentkezve, cookie-kban tárolt jelszó vagy hasonló nélkül.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Be van-e jelentkezve a látogató?
// Akkor igaz, ha a belepes_feldolgozas.php vagy a regisztracio_feldolgozas.php
// már beírta a $_SESSION['user_id']-t egy sikeres belépés/regisztráció után.
function bejelentkezve(): bool
{
    return isset($_SESSION['user_id']);
}

// A bejelentkezett felhasználó azonosítója (felhasznalok.id), vagy null,
// ha senki nincs bejelentkezve. Ezt írjuk be a bejelentesek.user_id oszlopba
// új bejelentés mentésekor.
function aktualis_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

// A bejelentkezett felhasználó neve, vagy null. Ezt jelenítjük meg a "Bejelentkezve: ..."
// szövegben, és ezt írjuk be automatikusan a bejelentés "Név" mezőjébe is.
function aktualis_user_nev(): ?string
{
    return $_SESSION['user_nev'] ?? null;
}

// Ellenőrzi, hogy a munkamenetben tárolt user_id valóban létezik-e még a
// felhasznalok táblában. Ha az adatbázist időközben törölték/újratöltötték,
// egy régi böngészős munkamenet érvénytelen user_id-t tartalmazhat, ami
// idegenkulcs-hibát okozna beszúráskor - ilyenkor ez a függvény kijelentkezteti
// a látogatót, ahelyett hogy a program hibával leállna.
//
// Ezt minden olyan oldal elején meg kell hívni (a db_config.php betöltése
// UTÁN, mert kell hozzá a $pdo kapcsolat), ahol bejelentkezve()/aktualis_user_id()-t
// használunk.
function session_ervenyesitese(PDO $pdo): void
{
    if (!bejelentkezve()) {
        return;
    }

    $stmt = $pdo->prepare('SELECT id FROM felhasznalok WHERE id = :id');
    $stmt->execute(['id' => aktualis_user_id()]);

    if (!$stmt->fetch()) {
        // A tárolt user_id nem létezik (már) a táblában -> "kijelentkeztetjük"
        // a látogatót a munkamenetből törölve az adatait.
        unset($_SESSION['user_id'], $_SESSION['user_nev']);
    }
}
