<?php
// Regisztrációs űrlap oldala. Az adatokat a regisztracio_feldolgozas.php dolgozza fel.
require 'auth.php';
require 'db_config.php';
session_ervenyesitese($pdo);

// Ha valaki már be van jelentkezve, nincs értelme újra megmutatni neki a
// regisztrációs formot - egyből a galériára irányítjuk.
if (bejelentkezve()) {
    header('Location: ../Frontend/galeria.php');
    exit;
}

// Hibás regisztrációs kísérlet esetén a regisztracio_feldolgozas.php ide
// irányít vissza egy flash üzenettel (lásd bivak-cserepesko.php a flash mechanizmusról).
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Regisztráció | Fotógaléria</title>
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
    max-width: 420px;
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

  main { max-width: 420px; width: 100%; }

  h1 {
    font-size: clamp(1.6rem, 4vw, 2.2rem);
    font-weight: 400;
    letter-spacing: 0.02em;
    margin: 0 0 1.5rem 0;
  }

  .panel {
    background: var(--panel);
    border-radius: 6px;
    padding: 1.75rem 2rem;
  }

  .flash {
    padding: 0.9rem 1.2rem;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
  }

  .flash.siker { background: rgba(232,176,75,0.12); color: var(--accent); border: 1px solid var(--accent); }
  .flash.hiba { background: rgba(224,128,128,0.12); color: var(--error); border: 1px solid var(--error); }

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
    width: 100%;
  }

  form button:hover { opacity: 0.9; }

  .switch {
    font-family: Arial, sans-serif;
    font-size: 0.85rem;
    color: var(--muted);
    margin-top: 1.5rem;
    text-align: center;
  }

  .switch a { color: var(--accent); text-decoration: none; }
  .switch a:hover { text-decoration: underline; }
</style>
</head>
<body>

  <a class="back-link" href="../Frontend/galeria.php">&larr; Vissza a galériához</a>

  <main>
    <h1>Regisztráció</h1>

    <div class="panel">
      <?php if ($flash): ?>
        <div class="flash <?= htmlspecialchars($flash['tipus']) ?>"><?= htmlspecialchars($flash['uzenet']) ?></div>
      <?php endif; ?>

      <form method="post" action="regisztracio_feldolgozas.php">
        <label>Neved
          <input type="text" name="nev" required maxlength="100" value="<?= htmlspecialchars($_POST['nev'] ?? '') ?>">
        </label>
        <label>E-mail cím
          <input type="email" name="email" required maxlength="150" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <label>Jelszó
          <input type="password" name="jelszo" required minlength="6">
        </label>
        <label>Jelszó megerősítése
          <input type="password" name="jelszo_megerosites" required minlength="6">
        </label>
        <button type="submit">Fiók létrehozása</button>
      </form>

      <div class="switch">Van már fiókod? <a href="belepes.php">Jelentkezz be</a></div>
    </div>
  </main>

</body>
</html>
