<?php
// Főoldal (galéria): innen lehet eljutni a két bivakhely részletoldalára.
// PHP-ból van (nem sima .html-ből), mert a fejlécben meg kell jeleníteni
// a bejelentkezés állapotát, és meg kell tudnunk jeleníteni a más oldalakról
// (belépés, kijelentkezés, regisztráció) érkező visszajelző üzeneteket.
require '../Backend/auth.php';
require '../Backend/db_config.php';
session_ervenyesitese($pdo);

// Lásd bivak-cserepesko.php: session flash üzenet kiolvasása és törlése.
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fotógaléria</title>
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
    padding: 4rem 1.5rem;
  }

  h1 {
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    font-weight: 400;
    letter-spacing: 0.02em;
    margin: 0 0 0.5rem 0;
    text-align: center;
  }

  .subtitle {
    color: var(--muted);
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 3rem;
  }

  .gallery {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 900px;
  }

  .photo-card {
    background: var(--panel);
    border: none;
    padding: 0;
    cursor: pointer;
    border-radius: 6px;
    overflow: hidden;
    width: 320px;
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .photo-card:hover,
  .photo-card:focus-visible {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.4);
  }

  .photo-card:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 3px;
  }

  .photo-card img {
    display: block;
    width: 100%;
    height: 220px;
    object-fit: cover;
  }

  .photo-card .caption {
    font-family: Arial, sans-serif;
    font-size: 0.8rem;
    color: var(--muted);
    padding: 0.7rem 1rem;
    text-align: left;
  }

  @media (prefers-reduced-motion: reduce) {
    .photo-card { transition: none; }
  }

  .top-bar {
    width: 100%;
    max-width: 900px;
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1rem;
  }

  .nav-auth {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
  }

  .nav-auth a { color: var(--accent); text-decoration: none; }
  .nav-auth a:hover { text-decoration: underline; }

  .flash {
    width: 100%;
    max-width: 900px;
    box-sizing: border-box;
    padding: 0.9rem 1.2rem;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
  }

  .flash.siker { background: rgba(232,176,75,0.12); color: var(--accent); border: 1px solid var(--accent); }
  .flash.hiba { background: rgba(224,128,128,0.12); color: var(--error); border: 1px solid var(--error); }
</style>
</head>
<body>

  <!-- Fejléc: bejelentkezés állapota, ugyanaz a minta mint a bivak-oldalakon -->
  <div class="top-bar">
    <div class="nav-auth">
      <?php if (bejelentkezve()): ?>
        Bejelentkezve: <strong><?= htmlspecialchars(aktualis_user_nev()) ?></strong>
        &middot; <a href="../Backend/kijelentkezes.php">Kijelentkezés</a>
      <?php else: ?>
        <a href="../Backend/belepes.php">Belépés</a> &middot; <a href="../Backend/regisztracio.php">Regisztráció</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pl. "Sikeresen kijelentkeztél" - a kijelentkezes.php / belepes_feldolgozas.php stb. állítja be -->
  <?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['tipus']) ?>"><?= htmlspecialchars($flash['uzenet']) ?></div>
  <?php endif; ?>

  <h1>Fotógaléria</h1>
  <div class="subtitle">Kattints egy képre a részletekért</div>

  <div class="gallery">
    <a class="photo-card" href="../Backend/bivak-cserepesko.php">
      <img src="images/Bivak/CserepesK.jpg" alt="Cserepes-kő">
      <div class="caption">Cserepes-kő</div>
    </a>

    <a class="photo-card" href="../Backend/bivak-toldi.php">
      <img src="images/Bivak/ToldiK.jpg" alt="Toldi kunyhó">
      <div class="caption">Toldi kunyhó</div>
    </a>
  </div>

</body>
</html>
