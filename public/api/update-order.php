<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['chyba' => 'Prístup zamietnutý.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['chyba' => 'Metóda nie je povolená.']);
    exit;
}

$id            = (int)($_POST['id'] ?? 0);
$novy_stav     = $_POST['stav'] ?? '';
$poznamka      = trim($_POST['poznamka_technika'] ?? '');
$finalna_cena  = (float)($_POST['finalna_cena'] ?? 0);
$stavOptions   = ['caka','diagnostika','oprava','testovanie','hotovo','zrusena'];

if (!$id || !in_array($novy_stav, $stavOptions)) {
    echo json_encode(['chyba' => 'Neplatné vstupné dáta.']);
    exit;
}

$pdo = getPDO();
$stary = $pdo->prepare("SELECT stav FROM objednavky WHERE id = ?");
$stary->execute([$id]);
$stary = $stary->fetchColumn();

if ($stary === false) {
    echo json_encode(['chyba' => 'Objednávka neexistuje.']);
    exit;
}

$pdo->prepare("UPDATE objednavky SET stav=?, poznamka_technika=?, finalna_cena=?, updated_at=NOW() WHERE id=?")
    ->execute([$novy_stav, $poznamka, $finalna_cena ?: null, $id]);

if ($novy_stav !== $stary) {
    $pdo->prepare("INSERT INTO stav_historia (objednavka_id, stav, poznamka) VALUES (?,?,?)")
        ->execute([$id, $novy_stav, $poznamka ?: 'Stav zmenený cez API']);
}

[$label] = stavLabel($novy_stav);
echo json_encode(['ok' => true, 'stav' => $novy_stav, 'stav_text' => $label]);
