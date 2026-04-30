<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$ticket = trim($_GET['ticket'] ?? '');
if (!$ticket) {
    echo json_encode(['chyba' => 'Chýba číslo objednávky.']);
    exit;
}

$stmt = getPDO()->prepare("SELECT ticket_id, zariadenie_model, stav, updated_at FROM objednavky WHERE ticket_id = ?");
$stmt->execute([$ticket]);
$o = $stmt->fetch();

if (!$o) {
    echo json_encode(['chyba' => 'Objednávka nebola nájdená.']);
    exit;
}

[$label] = stavLabel($o['stav']);
echo json_encode([
    'ticket_id'  => $o['ticket_id'],
    'zariadenie' => $o['zariadenie_model'],
    'stav'       => $o['stav'],
    'stav_text'  => $label,
    'updated_at' => $o['updated_at'],
]);
