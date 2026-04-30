<?php
/**
 * Opravimto.sk – Inštalátor databázy
 * Spustite raz: http://vasadomena.sk/setup.php
 * Po spustení VYMAŽTE tento súbor!
 */

$host   = 'localhost';
$dbname = 'opravimto';
$user   = 'root';
$pass   = '';

$log = [];
$ok  = true;

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
    // Rozdelíme na jednotlivé príkazy
    $statements = array_filter(
        array_map('trim', preg_split('/;\s*\n/', $schema)),
        fn($s) => strlen($s) > 5 && !str_starts_with(ltrim($s), '--')
    );

    foreach ($statements as $sql) {
        try {
            $pdo->exec($sql);
            $log[] = ['ok', substr($sql, 0, 60) . '...'];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'already exists') || str_contains($e->getMessage(), 'Duplicate')) {
                $log[] = ['skip', substr($sql, 0, 60) . '... (existuje)'];
            } else {
                $log[] = ['err', $e->getMessage()];
                $ok = false;
            }
        }
    }
} catch (PDOException $e) {
    $ok = false;
    $log[] = ['err', 'Pripojenie zlyhalo: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="sk">
<head>
<meta charset="utf-8"/>
<title>Setup – opravimto.sk</title>
<style>
  body { font-family: monospace; background: #161216; color: #e8e0e6; padding: 2rem; max-width: 800px; margin: 0 auto; }
  h1 { color: #72d6d8; }
  .ok   { color: #4ade80; }
  .skip { color: #facc15; }
  .err  { color: #f87171; }
  .box { background: #221e23; border: 1px solid #3d4949; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
  .warn { background: #2a1a00; border: 1px solid #EC9A29; border-radius: 8px; padding: 1rem; color: #ffb964; margin-top: 2rem; }
  a { color: #72d6d8; }
</style>
</head>
<body>
<h1>🔧 opravimto.sk – Inštalátor DB</h1>

<div class="box">
  <?php foreach ($log as [$type, $msg]): ?>
    <div class="<?= $type ?>">
      <?= $type === 'ok' ? '✓' : ($type === 'skip' ? '↷' : '✗') ?>
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($ok): ?>
  <div class="ok">✅ Databáza bola úspešne nainštalovaná!</div>
  <div class="box">
    <strong>Prihlasovacie údaje admin panelu:</strong><br/>
    E-mail: admin@opravimto.sk<br/>
    Heslo: Admin123!<br/><br/>
    <strong>Dôležité:</strong> Zmeňte heslo po prvom prihlásení!
  </div>
  <p><a href="/index.php">→ Prejsť na web</a> | <a href="/admin/login.php">→ Admin panel</a></p>
<?php else: ?>
  <div class="err">❌ Inštalácia zlyhala. Skontrolujte nastavenia v config/db.php</div>
<?php endif; ?>

<div class="warn">
  ⚠️ <strong>BEZPEČNOSŤ:</strong> Po úspešnej inštalácii <strong>VYMAŽTE</strong> tento súbor (setup.php)!
</div>
</body>
</html>
