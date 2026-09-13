-- Adatbázis és táblák létrehozása:
--   - felhasznalok: regisztrált fiókok
--   - bejelentesek: tervezett megszállások, fiókhoz kötve (ha be volt jelentkezve
--     a bejelentő), utólag szerkeszthető dátummal és megjegyzéssel

CREATE DATABASE IF NOT EXISTS fotogaleria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fotogaleria;

CREATE TABLE IF NOT EXISTS felhasznalok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nev VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    jelszo_hash VARCHAR(255) NOT NULL,
    letrehozva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_felhasznalok_email (email)
);

CREATE TABLE IF NOT EXISTS bejelentesek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    helyszin VARCHAR(100) NOT NULL,
    nev VARCHAR(100) NOT NULL,
    datum_tol DATE NOT NULL,
    datum_ig DATE NOT NULL,
    megjegyzes TEXT NULL,
    letrehozva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modositva TIMESTAMP NULL,
    INDEX idx_helyszin (helyszin),
    CONSTRAINT fk_bejelentesek_user
        FOREIGN KEY (user_id) REFERENCES felhasznalok(id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------------------
-- Meglévő telepítés frissítése
-- ---------------------------------------------------------------------------
-- Ha a `bejelentesek` tábla már létezett egy korábbi telepítésből (tehát a
-- fenti CREATE TABLE IF NOT EXISTS nem futott le újra), az alábbi ALTER
-- utasításokkal egészítheted ki a hiányzó oszlopokkal, adatvesztés nélkül.
-- Egyszerűbb megoldás egy vizsgaremeknél: dobd el a régi adatbázist
-- (DROP DATABASE fotogaleria;), majd futtasd le újra ezt a fájlt elejétől.
--
-- ALTER TABLE bejelentesek ADD COLUMN user_id INT NULL AFTER id;
-- ALTER TABLE bejelentesek ADD COLUMN megjegyzes TEXT NULL AFTER datum_ig;
-- ALTER TABLE bejelentesek ADD COLUMN modositva TIMESTAMP NULL;
-- ALTER TABLE bejelentesek
--     ADD CONSTRAINT fk_bejelentesek_user
--     FOREIGN KEY (user_id) REFERENCES felhasznalok(id) ON DELETE SET NULL;
