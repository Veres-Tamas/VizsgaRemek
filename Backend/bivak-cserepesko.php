<?php
// A Cserepes-kő bivakhely részletoldala:
//   - megjeleníti a helyszín adatait,
//   - form-ot ad a "tervezett időpont jelzéséhez" (POST -> mentes.php),
//   - listázza az eddigi bejelentéseket, a saját bejelentés(ek) mellett
//     "Szerkesztés" linkkel.
require 'auth.php';
require 'db_config.php';
session_ervenyesitese($pdo);

// Ez a konstans érték köti össze ezt az oldalt a bejelentesek.helyszin
// oszlopban tárolt adattal: a mentes.php ezt kapja meg rejtett mezőként,
// az alábbi lekérdezés pedig csak az ide tartozó sorokat kéri le.
$helyszin = 'Cserepes-kő';

// Csak ehhez a helyszínhez tartozó bejelentéseket kérjük le, a legkorábbi
// időponttal kezdve. A user_id-ra azért van szükség (nem csak megjelenítésre),
// hogy alább el tudjuk dönteni, kinek jelenjen meg a "Szerkesztés" link.
$stmt = $pdo->prepare('SELECT id, user_id, nev, datum_tol, datum_ig, megjegyzes FROM bejelentesek WHERE helyszin = :helyszin ORDER BY datum_tol ASC');
$stmt->execute(['helyszin' => $helyszin]);
$bejelentesek = $stmt->fetchAll();

// A mentes.php (vagy foglalas_szerkesztes.php) egy sikeres/hibás művelet után
// ide irányít vissza, és ebbe a session kulcsba teszi az üzenetet. Itt olvassuk
// ki, majd rögtön törüljük is - így csak egyszer, az oldal következő betöltésekor
// jelenik meg ("flash", azaz felvillanó üzenet).
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cserepes-kő | Fotógaléria</title>
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
    max-width: 900px;
    width: 100%;
    margin: 0 auto 2rem;
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--muted);
    text-decoration: none;
  }

  .back-link:hover,
  .back-link:focus-visible {
    color: var(--accent);
  }

  .top-bar {
    max-width: 900px;
    width: 100%;
    margin: 0 auto 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .top-bar .back-link { margin: 0; }

  .nav-auth {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
  }

  .nav-auth a { color: var(--accent); text-decoration: none; }
  .nav-auth a:hover { text-decoration: underline; }

  main {
    max-width: 900px;
    width: 100%;
  }

  h1 {
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    font-weight: 400;
    letter-spacing: 0.02em;
    margin: 0 0 1.75rem 0;
  }

  .hero-image {
    width: 100%;
    max-height: 560px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.45);
    display: block;
    margin-bottom: 2rem;
  }

  .description {
    background: var(--panel);
    border-radius: 6px;
    padding: 1.75rem 2rem;
    font-size: 1.05rem;
    line-height: 1.7;
  }

  .description p {
    margin: 0 0 1rem 0;
  }

  .description p:last-child {
    margin-bottom: 0;
  }

  .meta {
    font-family: Arial, sans-serif;
    font-size: 0.8rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 0.5rem;
  }

  .flash {
    padding: 0.9rem 1.2rem;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    margin-top: 2rem;
  }

  .flash.siker {
    background: rgba(232,176,75,0.12);
    color: var(--accent);
    border: 1px solid var(--accent);
  }

  .flash.hiba {
    background: rgba(224,128,128,0.12);
    color: var(--error);
    border: 1px solid var(--error);
  }

  .bejelentes, .lista {
    background: var(--panel);
    border-radius: 6px;
    padding: 1.75rem 2rem;
    margin-top: 2rem;
  }

  .bejelentes h2, .lista h2 {
    font-size: 1.3rem;
    font-weight: 400;
    margin: 0 0 0.5rem 0;
  }

  .hint {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
  }

  form label {
    display: block;
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
    margin-bottom: 1rem;
  }

  form input {
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

  form input:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 1px;
    border-color: var(--accent);
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
    margin-top: 0.5rem;
  }

  form button:hover {
    opacity: 0.9;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
  }

  th, td {
    text-align: left;
    padding: 0.6rem 0.5rem;
    border-bottom: 1px solid #2c2e35;
  }

  th {
    color: var(--muted);
    font-weight: normal;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
  }

  .empty {
    font-family: Arial, sans-serif;
    color: var(--muted);
    font-size: 0.9rem;
  }

  .megjegyzes-cell {
    color: var(--muted);
    max-width: 220px;
  }

  .edit-link {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--accent);
    text-decoration: none;
    white-space: nowrap;
  }

  .edit-link:hover { text-decoration: underline; }
</style>
</head>
<body>

  <!-- Fejléc: bal oldalt vissza a galériára, jobb oldalt a bejelentkezés
       állapota - vagy a felhasználó neve + kijelentkezés, vagy belépés/regisztráció linkek. -->
  <div class="top-bar">
    <a class="back-link" href="../Frontend/galeria.php">&larr; Vissza a galériához</a>
    <div class="nav-auth">
      <?php if (bejelentkezve()): ?>
        Bejelentkezve: <strong><?= htmlspecialchars(aktualis_user_nev()) ?></strong>
        &middot; <a href="kijelentkezes.php">Kijelentkezés</a>
      <?php else: ?>
        <a href="belepes.php">Belépés</a> &middot; <a href="regisztracio.php">Regisztráció</a>
      <?php endif; ?>
    </div>
  </div>

  <main>
    <div class="meta">Bivak</div>
    <h1>Cserepes-kő</h1>

    <img class="hero-image" src="../Frontend/images/Bivak/CserepesK.jpg" alt="Cserepes-kő">

    <div class="description">
      <p>Ide kerülhet a Cserepes-kő bivakhely részletes leírása: hol található, hogyan közelíthető meg, milyen jellegzetességei vannak, és milyen tapasztalatok kötődnek hozzá.</p>
      <p>Cseréld le ezt a szöveget a saját tartalmadra — akár egy rövid útibeszámoló, akár praktikus információk (megközelítés, vízforrás, alkalmasság éjszakázásra) formájában.</p>
    </div>

    <!-- Visszajelző (flash) üzenet a mentés/szerkesztés után, ha van -->
    <?php if ($flash): ?>
      <div class="flash <?= htmlspecialchars($flash['tipus']) ?>"><?= htmlspecialchars($flash['uzenet']) ?></div>
    <?php endif; ?>

    <!-- Új bejelentés form - a mentes.php dolgozza fel -->
    <section class="bejelentes">
      <h2>Tervezed, hogy itt szállsz meg?</h2>
      <p class="hint">
        Ez a hely hivatalosan nem foglalható. Ez a bejelentés csak jelzés másoknak, hogy mikor tervezed használni — nem garantálja, hogy szabad lesz.
        <?php if (!bejelentkezve()): ?>
          Ha <a href="regisztracio.php">regisztrálsz</a> vagy <a href="belepes.php">belépsz</a>, a bejelentésedet utólag is szerkesztheted (pl. ha valami hiányzott vagy sérült volt a helyszínen).
        <?php endif; ?>
      </p>
      <form method="post" action="mentes.php">
        <!-- Rejtett mezők: a mentes.php ezekből tudja, melyik helyszínről van szó,
             és sikeres mentés után melyik oldalra kell visszairányítani. -->
        <input type="hidden" name="helyszin" value="<?= htmlspecialchars($helyszin) ?>">
        <input type="hidden" name="vissza" value="bivak-cserepesko.php">

        <?php if (bejelentkezve()): ?>
          <!-- Bejelentkezve a név mező csak megjelenítés (disabled - nincs "name"
               attribútuma, tehát nem is küldi el a form): a mentes.php mindenképp
               a fiók nevét fogja használni, ez itt csak vizuális visszajelzés. -->
          <label>Neved
            <input type="text" value="<?= htmlspecialchars(aktualis_user_nev()) ?>" disabled>
          </label>
        <?php else: ?>
          <label>Neved
            <input type="text" name="nev" required maxlength="100">
          </label>
        <?php endif; ?>
        <label>Mettől
          <input type="date" name="datum_tol" required>
        </label>
        <label>Meddig
          <input type="date" name="datum_ig" required>
        </label>
        <button type="submit">Tervezett időpont jelzése</button>
      </form>
    </section>

    <!-- Meglévő bejelentések listája, dátum szerint rendezve -->
    <section class="lista">
      <h2>Tervezett megszállások</h2>
      <?php if (empty($bejelentesek)): ?>
        <p class="empty">Egyelőre senki nem jelzett tervezett időpontot.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Név</th><th>Mettől</th><th>Meddig</th><th>Megjegyzés</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($bejelentesek as $b): ?>
              <tr>
                <td><?= htmlspecialchars($b['nev']) ?></td>
                <td><?= htmlspecialchars(date('Y.m.d.', strtotime($b['datum_tol']))) ?></td>
                <td><?= htmlspecialchars(date('Y.m.d.', strtotime($b['datum_ig']))) ?></td>
                <!-- Ha még nincs megjegyzés (NULL), egy gondolatjelet mutatunk helyette -->
                <td class="megjegyzes-cell"><?= $b['megjegyzes'] !== null ? nl2br(htmlspecialchars($b['megjegyzes'])) : '—' ?></td>
                <td>
                  <!-- A "Szerkesztés" link csak a saját bejelentésnél jelenik meg:
                       be kell jelentkezve lenni, ÉS a sor user_id-jának egyeznie
                       kell a bejelentkezett felhasználóéval. -->
                  <?php if (bejelentkezve() && (int) $b['user_id'] === aktualis_user_id()): ?>
                    <a class="edit-link" href="foglalas_szerkesztes.php?id=<?= (int) $b['id'] ?>">Szerkesztés</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>

</body>
</html>
