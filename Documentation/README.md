# Bivak fotógaléria — projekt dokumentáció

## Mappastruktúra

```
vizsgaremek/
  Frontend/               Statikus tartalom (galéria oldal, képek)
    galeria.html
    images/
      Bivak/
        CserepesK.jpg
        ToldiK.jpg
  Backend/                Szerver oldali logika (PHP)
    bivak-cserepesko.php  Cserepes-kő részletes oldal + bejelentés form + lista
    bivak-toldi.php       Toldi kunyhó részletes oldal + bejelentés form + lista
    mentes.php            Form feldolgozó (validáció + adatbázisba írás)
    db_config.php         Adatbázis kapcsolódási adatok (PDO)
  Database/
    schema.sql            Adatbázis és tábla létrehozó szkript
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
   `http://localhost/vizsgaremek/Frontend/galeria.html`

## Adatfolyam

- A `galeria.html` két kártyáján keresztül lehet eljutni a `Backend/bivak-*.php`
  részletoldalakra.
- A részletoldalak `mentes.php`-nak POST-olják a bejelentés-formot (név, mettől,
  meddig dátum).
- A `mentes.php` validál, majd `INSERT`-eli az adatot a `bejelentesek` táblába, és
  visszairányít a küldő oldalra egy visszajelző üzenettel (session flash message).
- Minden bejelentés-oldal a saját helyszínéhez tartozó bejegyzéseket olvassa ki és
  jeleníti meg táblázatban, dátum szerint rendezve — ez mindenki számára látható,
  közös lista.

## Megjegyzés

A bejelentési funkció **nem hivatalos foglalás** — csak tájékoztató jellegű jelzés
arról, hogy valaki mikor tervezi használni az adott bivakhelyet.
