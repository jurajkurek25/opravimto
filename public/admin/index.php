<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pdo = getPDO();

// Štatistiky
$stats = $pdo->query("
  SELECT
    SUM(stav NOT IN ('hotovo','zrusena')) AS aktivne,
    SUM(priorita=1 AND stav NOT IN ('hotovo','zrusena')) AS urgentne,
    SUM(stav='caka') AS cakajuce,
    SUM(stav='hotovo') AS hotove
  FROM objednavky
")->fetch();

// Filter + search
$search  = trim($_GET['q'] ?? '');
$stavFilter = $_GET['stav'] ?? '';
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(ticket_id LIKE ? OR zakaznik_meno LIKE ? OR zakaznik_email LIKE ? OR zariadenie_model LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($stavFilter) {
    $where[] = "stav = ?";
    $params[] = $stavFilter;
}

$sql = "SELECT * FROM objednavky WHERE " . implode(' AND ', $where) . " ORDER BY priorita DESC, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$objednavky = $stmt->fetchAll();

$stavyFilter = [
    '' => 'Všetky',
    'caka' => 'Čaká',
    'diagnostika' => 'Diagnostika',
    'oprava' => 'Oprava',
    'testovanie' => 'Testovanie',
    'hotovo' => 'Hotovo',
    'zrusena' => 'Zrušené',
];
?>
<!DOCTYPE html>
<html class="dark" lang="sk">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin – Správa objednávok | opravimto.sk</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="/assets/css/app.css" rel="stylesheet"/>
<script>
tailwind.config = {
  darkMode:"class",
  theme:{ extend:{ colors:{
    "primary":"#72d6d8","on-primary":"#003738","primary-fixed":"#8ff3f4",
    "secondary":"#ffb964","on-secondary":"#482a00","secondary-container":"#cc8004",
    "error":"#ffb4ab","error-container":"#93000a",
    "background":"#161216","on-background":"#e8e0e6",
    "surface":"#161216","surface-container":"#221e23",
    "surface-container-high":"#2d292d","surface-container-low":"#1e1a1f",
    "surface-container-highest":"#383338","surface-variant":"#383338",
    "on-surface":"#e8e0e6","on-surface-variant":"#bdc9c9",
    "outline":"#879393","outline-variant":"#3d4949",
    "cta":"#EC9A29","cta-dark":"#2b1700"
  }}}
}
</script>
</head>
<body class="bg-[#161216] text-[#e8e0e6] font-[Inter,sans-serif] min-h-screen flex">

<!-- Sidebar -->
<nav class="hidden md:flex h-screen w-64 fixed left-0 top-0 bg-[#1A2B3C] border-r border-white/5 flex-col py-6 z-40">
  <div class="px-6 mb-8">
    <a href="/index.php" class="flex items-center gap-2 text-xl font-black tracking-tighter text-[#72d6d8]">
      <span class="material-symbols-outlined text-[#EC9A29]">build_circle</span>
      opravimto.sk
    </a>
    <div class="mt-2 text-xs text-[#bdc9c9]">Admin panel</div>
  </div>

  <div class="px-4 mb-4">
    <div class="text-xs font-semibold uppercase tracking-widest text-[#ffb964] mb-1">Prihlásený</div>
    <div class="text-sm text-[#e8e0e6]"><?= e($_SESSION['admin_meno']) ?></div>
  </div>

  <ul class="flex flex-col gap-1 flex-grow px-2">
    <?php
    $navItems = [
      ['/admin/index.php','dashboard','Prehľad objednávok', true],
      ['/admin/index.php?stav=caka','hourglass_empty','Čakajúce', false],
      ['/admin/index.php?stav=diagnostika','search','Diagnostika', false],
      ['/admin/index.php?stav=oprava','build','Prebieha oprava', false],
      ['/admin/index.php?stav=hotovo','check_circle','Hotové', false],
      ['/index.php','open_in_new','Zobraziť web', false],
    ];
    foreach ($navItems as [$href,$icon,$label,$active]): ?>
      <li>
        <a href="<?= $href ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all
          <?= $active ? 'bg-[#225566] text-white border-l-4 border-[#EC9A29]' : 'text-[#bdc9c9] hover:bg-[#225566]/50 hover:text-white' ?>">
          <span class="material-symbols-outlined text-[20px]"><?= $icon ?></span>
          <?= $label ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="px-4 mt-auto">
    <a href="/admin/logout.php" class="w-full flex items-center justify-center gap-2 border border-[#ffb4ab]/30 text-[#ffb4ab] py-2.5 rounded-lg text-sm hover:bg-[#ffb4ab]/10 transition-colors">
      <span class="material-symbols-outlined text-[18px]">logout</span> Odhlásiť sa
    </a>
  </div>
</nav>

<!-- Main -->
<main class="flex-1 md:ml-64 p-6 md:p-8 max-w-[calc(1280px+256px)] overflow-x-hidden">

  <!-- Top bar -->
  <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-white/10 pb-6 mb-8">
    <div>
      <h1 class="text-3xl md:text-4xl font-black text-[#e8e0e6]">Správa objednávok</h1>
      <p class="text-[#bdc9c9] mt-1 text-sm">Prehľad všetkých servisných požiadaviek</p>
    </div>
    <div class="flex gap-3 flex-wrap">
      <form method="GET" class="flex items-center gap-2">
        <?php if ($stavFilter): ?><input type="hidden" name="stav" value="<?= e($stavFilter) ?>"/><?php endif; ?>
        <div class="relative bg-surface-container border border-white/10 rounded-lg px-3 py-2 flex items-center gap-2">
          <span class="material-symbols-outlined text-[#879393] text-[18px]">search</span>
          <input name="q" value="<?= e($search) ?>" placeholder="Hľadať..." type="text"
                 class="bg-transparent border-none focus:ring-0 text-[#e8e0e6] text-sm p-0 w-44 placeholder-[#879393] outline-none"/>
        </div>
        <button type="submit" class="bg-[#2d292d] border border-white/10 text-[#e8e0e6] px-3 py-2 rounded-lg text-sm hover:bg-[#383338] transition-colors">Hľadať</button>
      </form>
      <a href="/rezervacia.php" class="bg-cta text-cta-dark font-bold text-sm px-4 py-2 rounded-lg hover:brightness-110 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span> Nová objednávka
      </a>
    </div>
  </header>

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $statCards = [
      ['Aktívne opravy', $stats['aktivne'] ?? 0, 'build', 'text-primary'],
      ['Urgentné', $stats['urgentne'] ?? 0, 'warning', 'text-error'],
      ['Čakajúce', $stats['cakajuce'] ?? 0, 'hourglass_empty', 'text-secondary'],
      ['Dokončené', $stats['hotove'] ?? 0, 'check_circle', 'text-primary'],
    ];
    foreach ($statCards as [$label,$count,$icon,$color]): ?>
      <div class="bg-surface-container border border-outline-variant rounded-xl p-5 flex flex-col justify-between">
        <div class="flex justify-between items-start mb-3">
          <span class="text-xs font-semibold uppercase tracking-wider text-[#bdc9c9]"><?= $label ?></span>
          <span class="material-symbols-outlined <?= $color ?> text-[20px]"><?= $icon ?></span>
        </div>
        <div class="text-4xl font-black text-[#e8e0e6] counter" data-target="<?= $count ?>"><?= $count ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Filter tabs -->
  <div class="flex gap-2 flex-wrap mb-4">
    <?php foreach ($stavyFilter as $val => $lbl): ?>
      <a href="?stav=<?= urlencode($val) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
         class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors <?= $stavFilter===$val ? 'bg-primary text-on-primary' : 'bg-surface-container border border-white/10 text-on-surface-variant hover:border-primary/50 hover:text-primary' ?>">
        <?= $lbl ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Objednávky tabuľka -->
  <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden">
    <div class="p-5 border-b border-outline-variant flex justify-between items-center bg-surface-container-high">
      <h2 class="text-lg font-semibold text-on-surface">Fronta objednávok</h2>
      <div class="flex gap-2">
        <button onclick="location.reload()" class="text-on-surface-variant hover:text-primary transition-colors p-1" title="Obnoviť">
          <span class="material-symbols-outlined">refresh</span>
        </button>
      </div>
    </div>

    <!-- Table header -->
    <div class="hidden md:grid grid-cols-12 gap-4 p-4 border-b border-outline-variant bg-surface-container-low text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
      <div class="col-span-1">ID</div>
      <div class="col-span-3">Zariadenie a problém</div>
      <div class="col-span-2">Zákazník</div>
      <div class="col-span-2">Stav</div>
      <div class="col-span-2">Priorita / Doprava</div>
      <div class="col-span-2 text-right">Akcie</div>
    </div>

    <div class="flex flex-col divide-y divide-outline-variant">
      <?php if (empty($objednavky)): ?>
        <div class="p-12 text-center text-on-surface-variant">
          <span class="material-symbols-outlined text-5xl block mb-3 text-outline">inbox</span>
          Žiadne objednávky
        </div>
      <?php endif; ?>

      <?php foreach ($objednavky as $o):
        [$stavText,$stavCss] = stavLabel($o['stav']); ?>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 items-center hover:bg-[#1A2B3C]/40 transition-colors group">
          <div class="col-span-1 text-xs font-mono text-primary"><?= e($o['ticket_id']) ?></div>

          <div class="col-span-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-surface-container-high border border-outline-variant flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-on-surface text-[20px]"><?= zariadeniIkona($o['zariadenie_typ']) ?></span>
            </div>
            <div>
              <div class="text-sm font-semibold text-on-surface"><?= e($o['zariadenie_model']) ?></div>
              <div class="text-xs text-on-surface-variant truncate max-w-[180px]"><?= e($o['problem_nazov']) ?></div>
            </div>
          </div>

          <div class="col-span-2">
            <div class="text-sm text-on-surface"><?= e($o['zakaznik_meno']) ?></div>
            <div class="text-xs text-on-surface-variant"><?= e($o['zakaznik_email']) ?></div>
          </div>

          <div class="col-span-2">
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border text-xs font-semibold <?= $stavCss ?>">
              <?php if (in_array($o['stav'], ['oprava','diagnostika'])): ?>
                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
              <?php endif; ?>
              <?= $stavText ?>
            </span>
          </div>

          <div class="col-span-2 flex gap-2 flex-wrap">
            <?php if ($o['priorita']): ?>
              <span class="px-2 py-0.5 bg-error/10 border border-error/20 text-error rounded text-xs font-bold">PRIORITA</span>
            <?php endif; ?>
            <span class="px-2 py-0.5 bg-surface-container-high border border-white/10 text-on-surface-variant rounded text-xs">
              <?= $o['doprava_typ'] === 'packeta' ? '📦 Packeta' : '🏪 Osobne' ?>
            </span>
          </div>

          <div class="col-span-2 flex justify-end gap-2">
            <a href="/admin/objednavka.php?id=<?= $o['id'] ?>"
               class="flex items-center gap-1 px-3 py-1.5 bg-primary/10 border border-primary/30 text-primary rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
              <span class="material-symbols-outlined text-[16px]">edit</span> Detail
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>
<script src="/assets/js/app.js"></script>
</body>
</html>
