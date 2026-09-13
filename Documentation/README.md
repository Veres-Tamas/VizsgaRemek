# Bivak fotógaléria — projekt dokumentáció

## Mappastruktúra

```
vizsgaremek/
  Frontend/                       Statikus/frontend tartalom (galéria oldal, képek)
    galeria.php                   Galéria + bejelentkezés-állapot a fejlécben
    images/
      Bivak/
        CserepesK.jpg
        ToldiK.jpg
  Backend/                        Szerver oldali logika (PHP)
    bivak-cserepesko.php          Cserepes-kő részletes oldal + bejelentés form + lista
    bivak-toldi.php               Toldi kunyhó részletes oldal + bejelentés form + lista
    mentes.php                    Bejelentés form feldolgozó (validáció + adatbázisba írás)
    db_config.php                 Adatbázis kapcsolódási adatok (PDO)
    auth.php                      Közös munkamenet-/bejelentkezés-segédfüggvények
    regisztracio.php               Regisztrációs űrlap
    regisztracio_feldolgozas.php   Regisztráció feldolgozó (jelszó hash, auto-belépés)
    belepes.php                    Bejelentkezési űrlap
    belepes_feldolgozas.php        Bejelentkezés feldolgozó
    kijelentkezes.php              Kijelentkezés (munkamenet törlése)
    foglalas_szerkesztes.php       Saját bejelentés utólagos szerkesztése (dátum + megjegyzés)
  Database/
    schema.sql            Adatbázis és táblák létrehozó szkript (felhasznalok, bejelentesek)
  Documentation/
    README.md             Ez a fájl
```

## Telepítés (XAMPP)

1. Másold az egész `vizsgaremek` mappát a XAMPP `htdocs` mappájába:
   `C:\xampp\htdocs\vizsgaremek\`

2. Indítsd el a XAMPP Control Panelben az **Apache** és a **MySQL** szolgáltatást.

3. Nyisd meg a `http://localhost/phpmyadmin` oldalt, és az **Import** fülön futtasd le a
   `Database/schema.sql` fájlt. Ez létrehozza a `fotogaleria` adatbázist és a
   `bejelentesek` táblát.

4. Ellenőrizd a `Backend/db_config.php` tartalmát — XAMPP alapértelmezés esetén nincs
   teendő (`host: localhost`, `user: root`, `pass:` üres).

5. Nyisd meg böngészőben:
   `http://localhost/vizsgaremek/Frontend/galeria.php`

## Adatfolyam

- A `galeria.php` két kártyáján keresztül lehet eljutni a `Backend/bivak-*.php`
  részletoldalakra. A galéria fejlécében látszik, hogy be vagy-e jelentkezve.
- A részletoldalak `mentes.php`-nak POST-olják a bejelentés-formot (mettől, meddig
  dátum, bejelentkezés nélkül a név is).
- A `mentes.php` validál, majd `INSERT`-eli az adatot a `bejelentesek` táblába
  (bejelentkezett felhasználónál a `user_id`-t is elmentve), és visszairányít a
  küldő oldalra egy visszajelző üzenettel (session flash message).
- Minden bejelentés-oldal a saját helyszínéhez tartozó bejegyzéseket olvassa ki és
  jeleníti meg táblázatban, dátum szerint rendezve, a megjegyzésekkel együtt — ez
  mindenki számára látható, közös lista.

## Fiókok és szerkeszthető bejelentések

- A `Backend/regisztracio.php` oldalon bárki létrehozhat fiókot (név, e-mail,
  jelszó). A jelszót `password_hash()` tárolja, sosem nyílt szövegként.
- A `Backend/belepes.php` oldalon lehet bejelentkezni; a `Backend/auth.php`
  tartalmazza a közös `bejelentkezve()`, `aktualis_user_id()` és
  `aktualis_user_nev()` segédfüggvényeket, ezeket minden érintett oldal betölti
  `session_start()` helyett.
- Bejelentkezve a bejelentés-formon a név mező a fiók nevére van rögzítve, és a
  `bejelentesek` sor a fiókhoz (`user_id`) kötődik.
- A listában a saját bejelentés mellett megjelenik egy "Szerkesztés" link, ami a
  `Backend/foglalas_szerkesztes.php`-ra visz. Itt a dátumok mellett egy utólagos
  **megjegyzés** mező is szerkeszthető — ide kerülhet például, hogy nem volt tűzifa,
  vagy valami sérült/hiányos volt a helyszínen. A megjegyzés mindenki számára
  látható lesz a táblázatban.
- A szerkesztő oldal ellenőrzi, hogy a bejelentés valóban a bejelentkezett
  felhasználóé (`user_id` egyezés) — más bejelentését senki nem tudja módosítani.
- A bejelentkezés nélkül leadott (régi vagy anonim) bejelentéseknek nincs
  gazdájuk (`user_id IS NULL`), ezért azok nem szerkeszthetők utólag.

## Megjegyzés

A bejelentési funkció **nem hivatalos foglalás** — csak tájékoztató jellegű jelzés
arról, hogy valaki mikor tervezi használni az adott bivakhelyet.
