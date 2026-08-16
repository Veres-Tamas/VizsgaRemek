-- Adatbázis és tábla létrehozása a tervezett megszállások (bejelentések) tárolásához
CREATE DATABASE IF NOT EXISTS fotogaleria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fotogaleria;

CREATE TABLE IF NOT EXISTS bejelentesek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    helyszin VARCHAR(100) NOT NULL,
    nev VARCHAR(100) NOT NULL,
    datum_tol DATE NOT NULL,
    datum_ig DATE NOT NULL,
    letrehozva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_helyszin (helyszin)
);
