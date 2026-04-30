<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (isAdmin()) { header('Location: /admin/index.php'); exit; }

$chyba = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $heslo = $_POST['heslo'] ?? '';
    $stmt = getPDO()->prepare("SELECT * FROM admini WHERE email = ?");
    $stmt->execute([$email]);
    $a = $stmt->fetch();
    if ($a && password_verify($heslo, $a['heslo'])) {
        $_SESSION['admin_id']   = $a['id'];
        $_SESSION['admin_meno'] = $a['meno'];
        header('Location: /admin/index.php');
        exit;
    }
    $chyba = 'Nesprávny e-mail alebo heslo.';
}
?>
<!DOCTYPE html>
<html class="dark" lang="sk">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin – opravimto.sk</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>tailwind.config={darkMode:"class",theme:{extend:{colors:{"background":"#161216","on-background":"#e8e0e6","surface-container":"#221e23","surface-container-high":"#2d292d","primary":"#72d6d8","on-primary":"#003738","outline":"#879393","outline-variant":"#3d4949","error":"#ffb4ab","cta":"#EC9A29","cta-dark":"#2b1700"}}}}</script>
</head>
<body class="bg-[#161216] text-[#e8e0e6] font-[Inter,sans-serif] min-h-screen flex items-center justify-center px-4">
<div class="w-full max-w-sm">
  <div class="text-center mb-8">
    <div class="w-16 h-16 rounded-xl bg-[#72d6d8]/10 flex items-center justify-center mx-auto mb-4 border border-[#72d6d8]/20">
      <span class="material-symbols-outlined text-[#72d6d8] text-3xl">admin_panel_settings</span>
    </div>
    <h1 class="text-3xl font-black">Administrácia</h1>
    <p class="text-[#bdc9c9] text-sm mt-1">opravimto.sk – servisný portál</p>
  </div>

  <?php if ($chyba): ?>
    <div class="mb-4 bg-red-900/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
      <span class="material-symbols-outlined text-[18px]">error</span> <?= htmlspecialchars($chyba) ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="bg-[#221e23] border border-white/10 rounded-xl p-6 flex flex-col gap-4">
    <div class="flex flex-col gap-1.5">
      <label class="text-sm font-medium" for="email">E-mail</label>
      <input type="email" id="email" name="email" required
             class="bg-[#2d292d] border border-white/10 rounded-lg px-4 py-3 text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors"/>
    </div>
    <div class="flex flex-col gap-1.5">
      <label class="text-sm font-medium" for="heslo">Heslo</label>
      <input type="password" id="heslo" name="heslo" required
             class="bg-[#2d292d] border border-white/10 rounded-lg px-4 py-3 text-[#e8e0e6] focus:outline-none focus:border-[#72d6d8] transition-colors"/>
    </div>
    <button type="submit" class="w-full bg-[#EC9A29] text-[#2b1700] font-bold py-3.5 rounded-xl hover:brightness-110 transition-all mt-2">
      Prihlásiť sa
    </button>
    <p class="text-xs text-center text-[#879393]">Demo: admin@opravimto.sk / Admin123!</p>
  </form>

  <div class="mt-4 text-center">
    <a href="/index.php" class="text-xs text-[#879393] hover:text-[#72d6d8] transition-colors">← Späť na web</a>
  </div>
</div>
</body>
</html>
