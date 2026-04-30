<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['chyba' => 'Metóda nie je povolená.']);
    exit;
}

$text        = trim($_POST['sprava'] ?? '');
$orderId     = (int)($_POST['objednavka_id'] ?? 0);
$fromAdmin   = isAdmin() ? 1 : 0;

if (!$text || !$orderId) {
    echo json_encode(['chyba' => 'Chýbajú povinné polia.']);
    exit;
}

if (!$fromAdmin && !isLoggedIn()) {
    echo json_encode(['chyba' => 'Nie ste prihlásený.']);
    exit;
}

// Overenie že objednávka existuje (a patrí zákazníkovi)
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT id FROM objednavky WHERE id = ?" . (!$fromAdmin ? " AND pouzivatel_id = ?" : ""));
$params = [$orderId];
if (!$fromAdmin) $params[] = $_SESSION['pouzivatel_id'];
$stmt->execute($params);
if (!$stmt->fetch()) {
    echo json_encode(['chyba' => 'Objednávka nebola nájdená.']);
    exit;
}

$pdo->prepare("INSERT INTO spravy (objednavka_id, od_admina, sprava) VALUES (?,?,?)")
    ->execute([$orderId, $fromAdmin, $text]);

echo json_encode([
    'ok'     => true,
    'sprava' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
    'cas'    => date('H:i'),
]);
