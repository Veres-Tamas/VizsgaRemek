<?php
// Egy adott bejelentés (dátumok + utólagos megjegyzés) szerkesztő oldala.
// Ezt a linket csak a bejelentés tulajdonosa látja a bivak-*.php listákban,
// de a szerver oldalon is le kell ellenőrizni a jogosultságot - hiszen
// bárki beírhatna kézzel egy másik id-t az URL-be.
require 'auth.php';
require 'db_config.php';
session_ervenyesitese($pdo);

// 1) Be van-e egyáltalán jelentkezve a látogató?
if (!bejelentkezve()) {
    $_SESSION['flash'] = [
        'tipus'  => 'hiba',
        'uzenet' => 'A szerkesztéshez be kell jelentkezned.',
    ];
    header('Location: belepes.php');
    exit;
}

// A szerkesztendő bejelentés azonosítója érkezhet linkből (GET, pl.
// foglalas_szerkesztes.php?id=5) vagy magának az űrlapnak a beküldéséből (POST).
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

// Kikeressük a bejelentést az adatbázisból az összes mezőjével (*).
$stmt = $pdo->prepare('SELECT * FROM bejelentesek WHERE id = :id');
$stmt->execute(['id' => $id]);
$bejelentes = $stmt->fetch();

// 2) Létezik-e ilyen bejelentés, ÉS valóban a bejelentkezett felhasználóé-e?
// Ez a legfontosabb jogosultsági ellenőrzés: enélkül bárki szerkeszthetné
// bárki más bejelentését, csak az id-t átírva az URL-ben.
if (!$bejelentes || (int) $bejelentes['user_id'] !== aktualis_user_id()) {
    $_SESSION['flash'] = [
        'tipus'  => 'hiba',
        'uzenet' => 'Ezt a bejelentést nem szerkesztheted.',
    ];
    header('Location: ../Frontend/galeria.php');
    exit;
}

// Mentés után arra a bivak-oldalra irányítunk vissza, ahonnan a bejelentés való.
$vissza = ($bejelentes['helyszin'] === 'Toldi kunyhó') ? 'bivak-toldi.php' : 'bivak-cserepesko.php';

$hiba_uzenet = null;

// Ha a form "Mentés" gombjával küldték be az oldalt (nem csak megnyitották
// a linket), akkor van mit feldolgozni.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mentes'])) {
    $datum_tol  = $_POST['datum_tol'] ?? '';
    $datum_ig   = $_POST['datum_ig'] ?? '';
    $megjegyzes = trim($_POST['megjegyzes'] ?? '');

    // --- Validáció, ugyanúgy mint az új bejelentésnél ---
    $hibak = [];

    if (!DateTime::createFromFormat('Y-m-d', $datum_tol) || !DateTime::createFromFormat('Y-m-d', $datum_ig)) {
        $hibak[] = 'Add meg mindkét dátumot érvényes formában!';
    } elseif ($datum_tol > $datum_ig) {
        $hibak[] = 'A kezdő dátum nem lehet később, mint a záró dátum.';
    }
    if (mb_strlen($megjegyzes) > 1000) {
        $hibak[] = 'A megjegyzés túl hosszú (legfeljebb 1000 karakter).';
    }

    if (empty($hibak)) {
        // A WHERE feltételben a user_id-t is ellenőrizzük (nem csak az id-t) -
        // ez egy extra biztonsági réteg: még ha valahogy hibásan jutna is idáig
        // a kód, akkor sem tudna frissülni más felhasználó bejelentése.
        $stmt = $pdo->prepare(
            'UPDATE bejelentesek
             SET datum_tol = :datum_tol, datum_ig = :datum_ig, megjegyzes = :megjegyzes, modositva = NOW()
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'datum_tol'  => $datum_tol,
            'datum_ig'   => $datum_ig,
            // Üres megjegyzés esetén inkább NULL-t mentünk, mint üres stringet -
            // így a listában egyszerűbb megkülönböztetni "nincs megjegyzés" esetet.
            'megjegyzes' => $megjegyzes !== '' ? $megjegyzes : null,
            'id'         => $id,
            'user_id'    => aktualis_user_id(),
        ]);

        $_SESSION['flash'] = [
            'tipus'  => 'siker',
            'uzenet' => 'A bejelentést frissítettük.',
        ];
        header('Location: ' . $vissza);
        exit;
    }

    $hiba_uzenet = implode(' ', $hibak);
    // Hiba esetén a beírt (még el nem mentett) adatokkal jelenítjük meg újra az űrlapot,
    // hogy a felhasználónak ne kelljen mindent újra begépelnie.
    $bejelentes['datum_tol']  = $datum_tol;
    $bejelentes['datum_ig']   = $datum_ig;
    $bejelentes['megjegyzes'] = $megjegyzes;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bejelentés szerkesztése | Fotógaléria</title>
<style>
  :root {
    --bg: #17181c;
    --panel: #1f2127;
    --text: #f2f0ea;
    --muted: #8b8d97;
    --accent: #e8b04b;
    --error: #e08080;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    min-height: 100vh;
    background: var(--bg);
    color: var(--text);
    font-family: 'Georgia', 'Times New Roman', serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 3rem 1.5rem 5rem;
  }

  .back-link {
    align-self: flex-start;
    max-width: 500px;
    width: 100%;
    margin: 0 auto 2rem;
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--muted);
    text-decoration: none;
  }

  .back-link:hover, .back-link:focus-visible { color: var(--accent); }

  main { max-width: 500px; width: 100%; }

  h1 {
    font-size: clamp(1.6rem, 4vw, 2.2rem);
    font-weight: 400;
    letter-spacing: 0.02em;
    margin: 0 0 0.3rem 0;
  }

  .meta {
    font-family: Arial, sans-serif;
    font-size: 0.8rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 1.5rem;
  }

  .panel {
    background: var(--panel);
    border-radius: 6px;
    padding: 1.75rem 2rem;
  }

  .hint {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
  }

  .flash {
    padding: 0.9rem 1.2rem;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
  }

  .flash.hiba { background: rgba(224,128,128,0.12); color: var(--error); border: 1px solid var(--error); }

  form label {
    display: block;
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
    margin-bottom: 1rem;
  }

  form input, form textarea {
    display: block;
    width: 100%;
    margin-top: 0.4rem;
    padding: 0.6rem 0.8rem;
    background: var(--bg);
    border: 1px solid #333;
    border-radius: 4px;
    color: var(--text);
    font-family: Arial, sans-serif;
    font-size: 0.95rem;
  }

  form textarea {
    resize: vertical;
    min-height: 100px;
    font-family: Arial, sans-serif;
  }

  form input:focus-visible, form textarea:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 1px;
    border-color: var(--accent);
  }

  .actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
  }

  form button {
    font-family: Arial, sans-serif;
    background: var(--accent);
    color: #1a1a1a;
    border: none;
    padding: 0.7rem 1.4rem;
    border-radius: 4px;
    font-size: 0.95rem;
    font-weight: bold;
    cursor: pointer;
  }

  form button:hover { opacity: 0.9; }

  .cancel-link {
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    color: var(--muted);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
  }

  .cancel-link:hover { color: var(--text); }
</style>
</head>
<body>

  <!-- Vissza-link: mindig a bejelentés helyszínéhez tartozó bivak oldalra mutat -->
  <a class="back-link" href="<?= htmlspecialchars($vissza) ?>">&larr; Vissza</a>

  <main>
    <div class="meta">Bejelentés szerkesztése &middot; <?= htmlspecialchars($bejelentes['helyszin']) ?></div>
    <h1><?= htmlspecialchars($bejelentes['nev']) ?></h1>

    <div class="panel">
      <p class="hint">
        Itt módosíthatod a tervezett időpontot, és utólagos megjegyzést fűzhetsz a
        bejelentéshez — például ha a helyszínen nem volt tűzifa, vagy valami
        sérült, hiányos volt. A megjegyzés a lista részeként mindenki számára
        látható lesz.
      </p>

      <?php if ($hiba_uzenet): ?>
        <div class="flash hiba"><?= htmlspecialchars($hiba_uzenet) ?></div>
      <?php endif; ?>

      <!-- Ugyanerre a fájlra küldi vissza magát (POST) - a fenti PHP kód
           dolgozza fel, ha a "mentes" gomb neve szerepel a beküldött adatok között. -->
      <form method="post" action="foglalas_szerkesztes.php">
        <!-- Rejtett mező: enélkül a szerver nem tudná, melyik bejelentést kell frissíteni. -->
        <input type="hidden" name="id" value="<?= (int) $bejelentes['id'] ?>">

        <label>Mettől
          <input type="date" name="datum_tol" required value="<?= htmlspecialchars($bejelentes['datum_tol']) ?>">
        </label>
        <label>Meddig
          <input type="date" name="datum_ig" required value="<?= htmlspecialchars($bejelentes['datum_ig']) ?>">
        </label>
        <label>Utólagos megjegyzés
          <textarea name="megjegyzes" maxlength="1000" placeholder="Pl.: nem volt tűzifa, a nyílászáró sérült volt..."><?= htmlspecialchars($bejelentes['megjegyzes'] ?? '') ?></textarea>
        </label>

        <div class="actions">
          <button type="submit" name="mentes" value="1">Mentés</button>
          <a class="cancel-link" href="<?= htmlspecialchars($vissza) ?>">Mégse</a>
        </div>
      </form>
    </div>
  </main>

</body>
</html>
