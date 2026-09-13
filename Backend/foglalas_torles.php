<?php

require 'auth.php';
require 'db_config.php';

session_ervenyesitese($pdo);

if (!bejelentkezve()) {
    $_SESSION['flash'] = [
        'tipus' => 'hiba',
        'uzenet' => 'A bejelentés törléséhez be kell jelentkezned.',
    ];

    header('Location: belepes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Frontend/galeria.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = [
        'tipus' => 'hiba',
        'uzenet' => 'Érvénytelen bejelentés.',
    ];

    header('Location: ../Frontend/galeria.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, user_id, helyszin
     FROM bejelentesek
     WHERE id = :id'
);
$stmt->execute(['id' => $id]);

$bejelentes = $stmt->fetch();

if (
    !$bejelentes ||
    (int) $bejelentes['user_id'] !== aktualis_user_id()
) {
    $_SESSION['flash'] = [
        'tipus' => 'hiba',
        'uzenet' => 'Ezt a bejelentést nem törölheted.',
    ];

    header('Location: ../Frontend/galeria.php');
    exit;
}

$vissza = ($bejelentes['helyszin'] === 'Toldi kunyhó')
    ? '../Frontend/bivak-toldi.php'
    : '../Frontend/bivak-cserepesko.php';

$stmt = $pdo->prepare(
    'DELETE FROM bejelentesek
     WHERE id = :id
       AND user_id = :user_id'
);

$stmt->execute([
    'id' => $id,
    'user_id' => aktualis_user_id(),
]);

$_SESSION['flash'] = [
    'tipus' => 'siker',
    'uzenet' => 'A bejelentést sikeresen töröltük.',
];

header('Location: ' . $vissza);
exit;
