<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Stav opravy';
$activeNav = 'stav';

$ticket = trim($_GET['ticket'] ?? '');
$objednavka = null;
$historia = [];

if ($ticket) {
    $stmt = getPDO()->prepare("SELECT * FROM objednavky WHERE ticket_id = ?");
    $stmt->execute([$ticket]);
    $objednavka = $stmt->fetch();

    if ($objednavka) {
        $h = getPDO()->prepare("SELECT * FROM stav_historia WHERE objednavka_id = ? ORDER BY created_at ASC");
        $h->execute([$objednavka['id']]);
        $historia = $h->fetchAll();
    }
}

$progressFazy = ['Prijaté','Diagnostika','Oprava','Testovanie','Pripravené'];
$stavProgress = ['caka'=>0,'diagnostika'=>1,'oprava'=>2,'testovanie'=>3,'hotovo'=>4,'zrusena'=>0];
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[88px] pb-20 px-6 md:px-8 max-w-[1280px] mx-auto w-full">
  <div class="max-w-2xl mx-auto">

    <div class="text-center mb-10 reveal-up">
      <h1 class="text-4xl font-black text-on-background mb-2">Stav opravy</h1>
      <p class="text-on-surface-variant">Zadajte číslo objednávky a zistite stav vašej opravy.</p>
    </div>

    <!-- Vyhľadávanie -->
    <form method="GET" class="flex gap-3 mb-10 reveal-up">
      <input type="text" name="ticket" value="<?= e($ticket) ?>"
             placeholder="Číslo objednávky (napr. OPR-1042-AB)"
             class="flex-1 bg-surface-container-high border border-outline/30 rounded-xl px-5 py-4 text-on-surface focus:outline-none focus:border-primary transition-colors font-mono placeholder:text-on-surface-variant/50 placeholder:font-sans"/>
      <button type="submit" class="bg-cta text-cta-dark font-bold px-6 py-4 rounded-xl hover:brightness-110 transition-all flex items-center gap-2">
        <span class="material-symbols-outlined">search</span>
        Hľadať
      </button>
    </form>

    <?php if ($ticket && !$objednavka): ?>
      <div class="bg-error/10 border border-error/30 text-error px-5 py-4 rounded-xl text-center reveal-up">
        <span class="material-symbols-outlined block text-3xl mb-2">search_off</span>
        Objednávka s číslom <strong><?= e($ticket) ?></strong> nebola nájdená.
      </div>
    <?php endif; ?>

    <?php if ($objednavka): ?>
      <!-- Výsledok -->
      <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden reveal-up">

        <!-- Header -->
        <div class="bg-surface-container-high p-6 border-b border-outline-variant">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="text-xs text-on-surface-variant font-semibold uppercase tracking-widest mb-1">Číslo objednávky</div>
              <div class="text-2xl font-black text-primary font-mono"><?= e($objednavka['ticket_id']) ?></div>
              <div class="text-sm text-on-surface-variant mt-1"><?= e($objednavka['zariadenie_model']) ?> – <?= e($objednavka['problem_nazov']) ?></div>
            </div>
            <?php [$label,$css] = stavLabel($objednavka['stav']); ?>
            <span class="px-3 py-1.5 rounded-lg text-sm font-bold border <?= $css ?> whitespace-nowrap"><?= $label ?></span>
          </div>
        </div>

        <!-- Progress -->
        <div class="p-6 border-b border-outline-variant">
          <?php $step = $stavProgress[$objednavka['stav']] ?? 0; ?>
          <div class="flex justify-between mb-3">
            <?php foreach ($progressFazy as $i => $faza): ?>
              <span class="text-xs font-semibold <?= $i <= $step ? 'text-primary' : 'text-on-surface-variant' ?>"><?= $faza ?></span>
            <?php endforeach; ?>
          </div>
          <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-[3px] bg-outline-variant w-full -z-10 rounded-full"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-[3px] bg-primary -z-10 rounded-full transition-all duration-1000"
                 style="width: <?= $step === 0 ? 0 : ($step/4)*100 ?>%"></div>
            <?php foreach ($progressFazy as $i => $faza): ?>
              <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all
                <?= $i < $step ? 'bg-primary' : ($i===$step ? 'bg-secondary-container border-2 border-secondary animate-pulse' : 'bg-surface-container border-2 border-outline-variant') ?>">
                <?php if ($i < $step): ?>
                  <span class="material-symbols-outlined text-on-primary text-[16px]">check</span>
                <?php elseif ($i === $step): ?>
                  <span class="w-2 h-2 rounded-full bg-secondary"></span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- História -->
        <div class="p-6">
          <h3 class="text-sm font-semibold text-on-surface-variant uppercase tracking-wider mb-4">História stavov</h3>
          <div class="flex flex-col gap-3">
            <?php foreach (array_reverse($historia) as $h): ?>
              <div class="flex gap-3 items-start">
                <div class="w-2 h-2 rounded-full bg-primary flex-shrink-0 mt-2"></div>
                <div>
                  <div class="text-sm font-semibold text-on-surface"><?= stavLabel($h['stav'])[0] ?></div>
                  <?php if ($h['poznamka']): ?>
                    <div class="text-xs text-on-surface-variant mt-0.5"><?= e($h['poznamka']) ?></div>
                  <?php endif; ?>
                  <div class="text-xs text-on-surface-variant/60 mt-0.5"><?= date('d.m.Y H:i', strtotime($h['created_at'])) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Cena -->
        <?php if ($objednavka['cena_od']): ?>
        <div class="p-6 border-t border-outline-variant bg-surface-container-low flex justify-between items-center">
          <span class="text-sm text-on-surface-variant">Odhadovaná cena opravy</span>
          <span class="text-xl font-black text-cta">
            <?= number_format($objednavka['cena_od'],2,',','') ?> – <?= number_format($objednavka['cena_do'],2,',','') ?> €
          </span>
        </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
