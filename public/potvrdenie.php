<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Potvrdenie objednávky';
$activeNav = '';

$ticketId = trim($_GET['ticket'] ?? '');
$objednavka = null;

if ($ticketId) {
    $stmt = getPDO()->prepare("SELECT * FROM objednavky WHERE ticket_id = ?");
    $stmt->execute([$ticketId]);
    $objednavka = $stmt->fetch();
}

if (!$objednavka) {
    header('Location: /index.php');
    exit;
}
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[88px] pb-20 px-6 md:px-8 max-w-[1280px] mx-auto w-full">

  <!-- Success header -->
  <section class="flex flex-col items-center text-center py-12 reveal-up">
    <div class="success-circle w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center mb-6 border border-primary/20">
      <span class="material-symbols-outlined text-primary text-[52px]" style="font-variation-settings:'FILL' 1">check_circle</span>
    </div>
    <h1 class="text-4xl md:text-5xl font-black text-on-background mb-3">Žiadosť potvrdená!</h1>
    <p class="text-lg text-on-surface-variant max-w-xl mb-6">
      Vaša žiadosť o opravu bola úspešne odoslaná. Do 2 hodín sa vám ozveme e-mailom.
    </p>
    <div class="inline-flex items-center gap-3 bg-surface-container-high border border-outline-variant rounded-xl px-6 py-3">
      <span class="text-sm text-on-surface-variant font-medium">Číslo objednávky</span>
      <span class="text-2xl font-black text-cta tracking-widest"><?= e($objednavka['ticket_id']) ?></span>
      <button onclick="navigator.clipboard.writeText('<?= e($objednavka['ticket_id']) ?>')"
              class="text-on-surface-variant hover:text-primary transition-colors ml-2" title="Kopírovať">
        <span class="material-symbols-outlined text-[18px]">content_copy</span>
      </button>
    </div>
  </section>

  <div class="grid grid-cols-1 md:grid-cols-12 gap-6 max-w-5xl mx-auto">

    <!-- Left: pokyny + timeline -->
    <div class="md:col-span-7 flex flex-col gap-6">

      <!-- Zhrnutie objednávky -->
      <section class="bg-surface-container rounded-xl border border-outline-variant p-6 reveal-up">
        <h2 class="text-xl font-semibold text-on-surface mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">receipt_long</span> Zhrnutie objednávky
        </h2>
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div><span class="text-on-surface-variant">Zariadenie</span><div class="font-semibold text-on-surface mt-0.5"><?= e($objednavka['zariadenie_typ']) ?> – <?= e($objednavka['zariadenie_model']) ?></div></div>
          <div><span class="text-on-surface-variant">Problém</span><div class="font-semibold text-on-surface mt-0.5"><?= e($objednavka['problem_nazov']) ?></div></div>
          <div><span class="text-on-surface-variant">Zákazník</span><div class="font-semibold text-on-surface mt-0.5"><?= e($objednavka['zakaznik_meno']) ?></div></div>
          <div><span class="text-on-surface-variant">E-mail</span><div class="font-semibold text-on-surface mt-0.5"><?= e($objednavka['zakaznik_email']) ?></div></div>
          <div><span class="text-on-surface-variant">Doprava</span><div class="font-semibold text-on-surface mt-0.5"><?= $objednavka['doprava_typ']==='packeta' ? 'Packeta' : 'Osobné odovzdanie' ?></div></div>
          <div><span class="text-on-surface-variant">Priorita</span><div class="mt-0.5"><?= $objednavka['priorita'] ? '<span class="text-cta font-bold">Áno (+35 €)</span>' : '<span class="text-on-surface">Štandardná</span>' ?></div></div>
        </div>
      </section>

      <!-- Pokyny na balenie -->
      <section class="bg-surface-container rounded-xl border border-outline-variant p-6 reveal-up">
        <h2 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">inventory_2</span> Pokyny na balenie
        </h2>
        <p class="text-sm text-on-surface-variant mb-5">Postupujte podľa týchto krokov, aby vaše zariadenie dorazilo bezpečne.</p>
        <div class="flex flex-col gap-5">
          <?php
          $kroky = [
            ['Zálohovanie a reset', 'Zálohujte všetky dáta. Ak je to možné, vykonajte továrenský reset kvôli ochrane súkromia.'],
            ['Zabaľte zariadenie', 'Zabaľte zariadenie do aspoň dvoch vrstiev bublinkovej fólie. Neprikladajte príslušenstvo, pokiaľ o to nepožiadame.'],
            ['Uzatvorte balík', 'Vložte do pevnej kartónovej krabice, vyplňte prázdne miesta papierom alebo pienuškou. Zalepte páskou.'],
          ];
          foreach ($kroky as $i => [$tit,$text]): ?>
            <div class="flex gap-4">
              <div class="w-8 h-8 rounded-full bg-surface-container-highest border border-outline-variant flex items-center justify-center flex-shrink-0 text-sm font-bold text-on-surface">
                <?= $i+1 ?>
              </div>
              <div>
                <h4 class="font-semibold text-on-surface mb-1"><?= $tit ?></h4>
                <p class="text-sm text-on-surface-variant"><?= $text ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Časový plán -->
      <section class="bg-surface-container rounded-xl border border-outline-variant p-6 reveal-up">
        <h2 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">timeline</span> Časový plán opravy
        </h2>
        <div class="relative">
          <div class="absolute left-[15px] top-2 bottom-2 w-px bg-outline-variant"></div>
          <div class="flex flex-col gap-5">
            <?php
            $fazy = [
              ['local_shipping','Čakáme na zariadenie','Odovzdajte alebo odošlite zariadenie na určené miesto.', true],
              ['build','Diagnostika','Naši technici zariadenie analyzujú a potvrdia cenu opravy.', false],
              ['task_alt','Oprava a vrátenie','Zariadenie opravíme a odošleme späť alebo pripravíme na vyzdvihnutie.', false],
            ];
            foreach ($fazy as [$icon,$tit,$text,$active]): ?>
              <div class="relative flex items-start gap-4 <?= $active ? '' : 'opacity-50' ?>">
                <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 ring-4 ring-surface-container flex-shrink-0
                  <?= $active ? 'bg-primary' : 'bg-surface-container-highest border-2 border-outline-variant' ?>">
                  <span class="material-symbols-outlined <?= $active ? 'text-on-primary' : 'text-on-surface-variant' ?> text-[16px]"><?= $icon ?></span>
                </div>
                <div class="pt-1">
                  <h4 class="text-sm font-semibold <?= $active ? 'text-primary' : 'text-on-surface' ?>"><?= $tit ?></h4>
                  <p class="text-xs text-on-surface-variant mt-0.5"><?= $text ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </div>

    <!-- Right: doprava + akcie -->
    <div class="md:col-span-5 flex flex-col gap-6">

      <?php if ($objednavka['doprava_typ'] === 'packeta'): ?>
      <section class="bg-surface-container rounded-xl border border-outline-variant overflow-hidden reveal-right">
        <div class="p-4 bg-surface-container-high border-b border-outline-variant flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-cta">local_mall</span>
            <h3 class="font-semibold text-on-surface">Spôsob dopravy</h3>
          </div>
          <span class="text-xs px-2 py-1 rounded bg-cta/10 text-cta border border-cta/20 uppercase tracking-wider font-bold">Packeta</span>
        </div>
        <div class="p-5 flex flex-col gap-4">
          <p class="text-sm text-on-surface-variant">Zanesite zabalené zariadenie na akýkoľvek Packeta box a zadajte kód operátorovi.</p>
          <div class="bg-surface-dim rounded-lg p-5 border border-outline-variant flex flex-col items-center gap-2">
            <span class="text-xs text-on-surface-variant uppercase tracking-widest font-semibold">Váš kód odovzdania</span>
            <span class="text-4xl font-black text-primary tracking-widest" id="packeta-code">Z79-X42</span>
            <button onclick="navigator.clipboard.writeText('Z79-X42')"
                    class="text-cta hover:text-yellow-300 transition-colors text-sm font-medium flex items-center gap-1">
              <span class="material-symbols-outlined text-[16px]">content_copy</span> Kopírovať kód
            </button>
          </div>
        </div>
      </section>
      <?php else: ?>
      <section class="bg-surface-container rounded-xl border border-outline-variant p-5 reveal-right">
        <div class="flex items-center gap-2 mb-3">
          <span class="material-symbols-outlined text-primary">storefront</span>
          <h3 class="font-semibold text-on-surface">Osobné odovzdanie</h3>
        </div>
        <p class="text-sm text-on-surface-variant mb-3">Prineste zariadenie na adresu:</p>
        <div class="bg-surface-container-high rounded-lg p-4 text-sm text-on-surface border border-white/10">
          <div class="font-semibold">opravimto.sk servisné centrum</div>
          <div class="text-on-surface-variant mt-1">Hlavná 42, 811 01 Bratislava</div>
          <div class="text-on-surface-variant">Po – Pi: 9:00 – 18:00</div>
        </div>
      </section>
      <?php endif; ?>

      <!-- Akcie -->
      <div class="flex flex-col gap-3 reveal-right">
        <a href="/stav.php?ticket=<?= urlencode($objednavka['ticket_id']) ?>"
           class="w-full bg-cta text-cta-dark font-bold py-4 rounded-xl text-center hover:shadow-[0_0_20px_rgba(236,154,41,0.3)] transition-all flex items-center justify-center gap-2">
          Sledovať stav opravy <span class="material-symbols-outlined">arrow_forward</span>
        </a>
        <a href="/index.php"
           class="w-full border border-primary text-primary font-bold py-4 rounded-xl text-center hover:bg-primary/10 transition-all flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-[18px]">home</span> Späť na úvod
        </a>
        <button onclick="window.print()"
                class="w-full border border-white/10 text-on-surface-variant font-medium py-3 rounded-xl text-center hover:bg-surface-container-high transition-all flex items-center justify-center gap-2 text-sm">
          <span class="material-symbols-outlined text-[18px]">print</span> Vytlačiť potvrdenie
        </button>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<style>
.success-circle { animation: successPop 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
@keyframes successPop {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
</style>
