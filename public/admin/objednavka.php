<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /admin/index.php'); exit; }

$o = $pdo->prepare("SELECT * FROM objednavky WHERE id = ?");
$o->execute([$id]);
$objednavka = $o->fetch();
if (!$objednavka) { header('Location: /admin/index.php'); exit; }

$historia = $pdo->prepare("SELECT * FROM stav_historia WHERE objednavka_id = ? ORDER BY created_at DESC");
$historia->execute([$id]);
$historia = $historia->fetchAll();

$spravy = $pdo->prepare("SELECT * FROM spravy WHERE objednavka_id = ? ORDER BY created_at ASC");
$spravy->execute([$id]);
$spravy = $spravy->fetchAll();

$uspech = '';
$chyba  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcia = $_POST['akcia'] ?? '';

    if ($akcia === 'update') {
        $novy_stav     = $_POST['stav'] ?? $objednavka['stav'];
        $poznamka_tech = trim($_POST['poznamka_technika'] ?? '');
        $finalna_cena  = (float)($_POST['finalna_cena'] ?? 0);
        $priorita      = isset($_POST['priorita']) ? 1 : 0;

        $pdo->prepare("UPDATE objednavky SET stav=?, poznamka_technika=?, finalna_cena=?, priorita=?, updated_at=NOW() WHERE id=?")
            ->execute([$novy_stav, $poznamka_tech, $finalna_cena ?: null, $priorita, $id]);

        if ($novy_stav !== $objednavka['stav']) {
            $pdo->prepare("INSERT INTO stav_historia (objednavka_id, stav, poznamka) VALUES (?,?,?)")
                ->execute([$id, $novy_stav, 'Stav zmenený administrátorom']);
        }

        $uspech = 'Objednávka bola aktualizovaná.';
        $o->execute([$id]);
        $objednavka = $o->fetch();

    } elseif ($akcia === 'sprava') {
        $text = trim($_POST['sprava'] ?? '');
        if ($text) {
            $pdo->prepare("INSERT INTO spravy (objednavka_id, od_admina, sprava) VALUES (?,1,?)")
                ->execute([$id, $text]);
            $uspech = 'Správa odoslaná.';
        }
        $spravy = $pdo->prepare("SELECT * FROM spravy WHERE objednavka_id = ? ORDER BY created_at ASC");
        $spravy->execute([$id]);
        $spravy = $spravy->fetchAll();
    }
}

$stavOptions = ['caka','diagnostika','oprava','testovanie','hotovo','zrusena'];
?>
<!DOCTYPE html>
<html class="dark" lang="sk">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Detail objednávky <?= e($objednavka['ticket_id']) ?> | Admin</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{colors:{
  "primary":"#72d6d8","on-primary":"#003738","error":"#ffb4ab",
  "background":"#161216","on-background":"#e8e0e6",
  "surface-container":"#221e23","surface-container-high":"#2d292d",
  "surface-container-low":"#1e1a1f","surface-container-highest":"#383338",
  "on-surface":"#e8e0e6","on-surface-variant":"#bdc9c9",
  "outline":"#879393","outline-variant":"#3d4949",
  "secondary":"#ffb964","cta":"#EC9A29","cta-dark":"#2b1700"
}}}}</script>
</head>
<body class="bg-[#161216] text-[#e8e0e6] font-[Inter,sans-serif] min-h-screen flex">

<!-- Sidebar (zjednodušená) -->
<nav class="hidden md:flex h-screen w-56 fixed left-0 top-0 bg-[#1A2B3C] border-r border-white/5 flex-col py-6 z-40">
  <div class="px-5 mb-6">
    <a href="/index.php" class="text-lg font-black tracking-tighter text-[#72d6d8]">opravimto.sk</a>
    <div class="text-xs text-[#bdc9c9] mt-0.5">Admin panel</div>
  </div>
  <ul class="flex flex-col gap-1 px-2 flex-grow">
    <li><a href="/admin/index.php" class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm text-[#bdc9c9] hover:bg-[#225566]/50 hover:text-white transition-all">
      <span class="material-symbols-outlined text-[18px]">arrow_back</span> Späť na zoznam
    </a></li>
  </ul>
  <div class="px-4">
    <a href="/admin/logout.php" class="flex items-center gap-2 border border-red-500/20 text-red-400 py-2 px-4 rounded-lg text-sm hover:bg-red-500/10 transition-colors">
      <span class="material-symbols-outlined text-[16px]">logout</span> Odhlásiť
    </a>
  </div>
</nav>

<main class="flex-1 md:ml-56 p-6 md:p-8">

  <div class="mb-6 flex items-center gap-3">
    <a href="/admin/index.php" class="text-[#bdc9c9] hover:text-primary transition-colors">
      <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
      <h1 class="text-2xl font-black">Detail objednávky</h1>
      <div class="text-sm font-mono text-[#72d6d8]"><?= e($objednavka['ticket_id']) ?></div>
    </div>
    <?php if ($objednavka['priorita']): ?>
      <span class="ml-2 px-3 py-1 bg-red-900/20 border border-red-500/30 text-red-400 text-xs font-bold rounded-full flex items-center gap-1">
        <span class="material-symbols-outlined text-[14px]">warning</span> PRIORITA
      </span>
    <?php endif; ?>
  </div>

  <?php if ($uspech): ?>
    <div class="mb-4 bg-green-900/20 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
      <span class="material-symbols-outlined text-[18px]">check_circle</span> <?= e($uspech) ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Info + editácia -->
    <div class="lg:col-span-2 flex flex-col gap-6">

      <!-- Info karta -->
      <div class="bg-[#221e23] border border-white/10 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">info</span> Detaily objednávky
        </h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <?php foreach ([
            ['Zákazník',    $objednavka['zakaznik_meno']],
            ['E-mail',      $objednavka['zakaznik_email']],
            ['Telefón',     $objednavka['zakaznik_telefon'] ?: '—'],
            ['Zariadenie',  $objednavka['zariadenie_typ'].' – '.$objednavka['zariadenie_model']],
            ['Problém',     $objednavka['problem_nazov']],
            ['Doprava',     $objednavka['doprava_typ']==='packeta'?'Packeta':'Osobné odovzdanie'],
            ['Vytvorené',   date('d.m.Y H:i', strtotime($objednavka['created_at']))],
            ['Cena (odhad)', $objednavka['cena_od']?number_format($objednavka['cena_od'],2,',','').' – '.number_format($objednavka['cena_do'],2,',','').' €':'—'],
          ] as [$l,$v]): ?>
            <div>
              <div class="text-xs text-[#bdc9c9] mb-0.5"><?= $l ?></div>
              <div class="font-medium text-[#e8e0e6]"><?= e($v) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($objednavka['problem_popis']): ?>
          <div class="mt-4 pt-4 border-t border-white/10">
            <div class="text-xs text-[#bdc9c9] mb-1">Podrobný popis</div>
            <p class="text-sm text-[#e8e0e6]"><?= nl2br(e($objednavka['problem_popis'])) ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Editácia stavu -->
      <form method="POST" class="bg-[#221e23] border border-white/10 rounded-xl p-6">
        <input type="hidden" name="akcia" value="update"/>
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">edit</span> Aktualizovať objednávku
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-[#bdc9c9]">Stav opravy</label>
            <select name="stav" class="bg-[#2d292d] border border-white/10 rounded-lg px-4 py-3 text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors">
              <?php foreach ($stavOptions as $s):
                [$sl] = stavLabel($s); ?>
                <option value="<?= $s ?>" <?= $objednavka['stav']===$s?'selected':'' ?>><?= $sl ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-[#bdc9c9]">Finálna cena (€)</label>
            <input type="number" name="finalna_cena" step="0.01" min="0"
                   value="<?= $objednavka['finalna_cena'] ?? '' ?>"
                   placeholder="napr. 129.00"
                   class="bg-[#2d292d] border border-white/10 rounded-lg px-4 py-3 text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors"/>
          </div>

          <div class="sm:col-span-2 flex flex-col gap-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-[#bdc9c9]">Poznámka technika (zobrazí sa zákazníkovi)</label>
            <textarea name="poznamka_technika" rows="3"
                      placeholder="napr. Displej bol úspešne vymenený..."
                      class="bg-[#2d292d] border border-white/10 rounded-lg px-4 py-3 text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors resize-none placeholder:text-[#879393]"><?= e($objednavka['poznamka_technika'] ?? '') ?></textarea>
          </div>

          <div class="flex items-center gap-3">
            <input type="checkbox" name="priorita" id="priorita" class="w-4 h-4 accent-[#72d6d8]" <?= $objednavka['priorita'] ? 'checked' : '' ?>/>
            <label for="priorita" class="text-sm text-[#e8e0e6]">Označiť ako prioritnú</label>
          </div>
        </div>

        <button type="submit" class="mt-5 bg-[#EC9A29] text-[#2b1700] font-bold px-6 py-3 rounded-xl hover:brightness-110 transition-all flex items-center gap-2">
          <span class="material-symbols-outlined text-[18px]">save</span> Uložiť zmeny
        </button>
      </form>

      <!-- História stavov -->
      <div class="bg-[#221e23] border border-white/10 rounded-xl p-6">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">history</span> História stavov
        </h2>
        <div class="flex flex-col gap-3">
          <?php foreach ($historia as $h):
            [$sl] = stavLabel($h['stav']); ?>
            <div class="flex gap-3 items-start text-sm">
              <div class="w-2 h-2 rounded-full bg-primary flex-shrink-0 mt-2"></div>
              <div class="flex-1">
                <div class="font-semibold text-[#e8e0e6]"><?= $sl ?></div>
                <?php if ($h['poznamka']): ?>
                  <div class="text-xs text-[#bdc9c9]"><?= e($h['poznamka']) ?></div>
                <?php endif; ?>
                <div class="text-xs text-[#879393]"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Chat sidebar -->
    <div class="flex flex-col gap-6">
      <div class="bg-[#221e23] border border-white/10 rounded-xl p-5 flex flex-col">
        <h2 class="text-base font-semibold mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[20px]">forum</span> Komunikácia so zákazníkom
        </h2>

        <div class="flex flex-col gap-3 max-h-80 overflow-y-auto mb-4 pr-1" id="chat">
          <?php foreach ($spravy as $s): ?>
            <div class="<?= $s['od_admina'] ? 'self-end bg-primary/10 border border-primary/20 rounded-xl rounded-tr-none' : 'self-start bg-[#1A2B3C] border border-white/10 rounded-xl rounded-tl-none' ?> p-3 max-w-full">
              <div class="text-xs font-semibold mb-1 <?= $s['od_admina'] ? 'text-primary' : 'text-[#ffb964]' ?>">
                <?= $s['od_admina'] ? 'Admin' : 'Zákazník' ?>
              </div>
              <p class="text-sm text-[#e8e0e6]"><?= nl2br(e($s['sprava'])) ?></p>
              <div class="text-xs text-[#879393] mt-1 text-right"><?= date('H:i d.m.', strtotime($s['created_at'])) ?></div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($spravy)): ?>
            <p class="text-sm text-[#bdc9c9] text-center py-4">Žiadne správy.</p>
          <?php endif; ?>
        </div>

        <form method="POST" class="flex gap-2">
          <input type="hidden" name="akcia" value="sprava"/>
          <textarea name="sprava" rows="2" placeholder="Napísať zákazníkovi..."
                    class="flex-1 bg-[#2d292d] border border-white/10 rounded-lg px-3 py-2 text-sm text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors resize-none placeholder:text-[#879393]"></textarea>
          <button type="submit" class="bg-primary text-on-primary px-4 rounded-lg hover:bg-primary-fixed transition-colors self-end flex-shrink-0 flex items-center justify-center h-10">
            <span class="material-symbols-outlined text-[18px]">send</span>
          </button>
        </form>
      </div>

      <!-- Rýchle akcie -->
      <div class="bg-[#221e23] border border-white/10 rounded-xl p-5">
        <h2 class="text-base font-semibold mb-3">Rýchle akcie</h2>
        <div class="flex flex-col gap-2">
          <?php foreach ([
            ['diagnostika','Nastaviť: Diagnostika','search'],
            ['oprava','Nastaviť: Oprava','build'],
            ['hotovo','Nastaviť: Hotovo','check_circle'],
          ] as [$s,$l,$i]): ?>
            <form method="POST">
              <input type="hidden" name="akcia" value="update"/>
              <input type="hidden" name="stav" value="<?= $s ?>"/>
              <input type="hidden" name="finalna_cena" value="<?= $objednavka['finalna_cena'] ?? '' ?>"/>
              <input type="hidden" name="poznamka_technika" value="<?= e($objednavka['poznamka_technika'] ?? '') ?>"/>
              <?php if ($objednavka['priorita']): ?><input type="hidden" name="priorita" value="1"/><?php endif; ?>
              <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 bg-[#2d292d] border border-white/10 rounded-lg text-sm hover:bg-[#383338] transition-colors">
                <span class="material-symbols-outlined text-[#72d6d8] text-[18px]"><?= $i ?></span>
                <?= $l ?>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</main>
<script>
const chat = document.getElementById('chat');
if (chat) chat.scrollTop = chat.scrollHeight;
</script>
</body>
</html>
