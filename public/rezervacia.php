<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Rezervovať opravu';
$activeNav = 'opravovna';

$chyba = '';
$uspech = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $meno     = trim($_POST['meno'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefon  = trim($_POST['telefon'] ?? '');
    $zar_typ  = trim($_POST['zariadenie_typ'] ?? '');
    $zar_model= trim($_POST['zariadenie_model'] ?? '');
    $prob_naz = trim($_POST['problem_nazov'] ?? '');
    $prob_pop = trim($_POST['problem_popis'] ?? '');
    $prob_kat = trim($_POST['problem_kategoria'] ?? '');
    $priorita = isset($_POST['priorita']) ? 1 : 0;
    $doprava  = in_array($_POST['doprava'] ?? '', ['osobne','packeta']) ? $_POST['doprava'] : 'osobne';

    if (!$meno || !$email || !$zar_typ || !$zar_model || !$prob_naz) {
        $chyba = 'Vyplňte prosím všetky povinné polia.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $chyba = 'Zadajte platnú e-mailovú adresu.';
    } else {
        $pdo = getPDO();
        $ticketId = genTicketId();

        // Ceny podla zariadenia
        $ceny = ['od' => 80.00, 'do' => 150.00];
        if ($priorita) { $ceny['od'] += 35; $ceny['do'] += 35; }

        $pouzivatel_id = $_SESSION['pouzivatel_id'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO objednavky
              (ticket_id, pouzivatel_id, zakaznik_meno, zakaznik_email, zakaznik_telefon,
               zariadenie_typ, zariadenie_model, problem_nazov, problem_popis, problem_kategoria,
               priorita, doprava_typ, cena_od, cena_do)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $ticketId, $pouzivatel_id, $meno, $email, $telefon,
            $zar_typ, $zar_model, $prob_naz, $prob_pop, $prob_kat,
            $priorita, $doprava, $ceny['od'], $ceny['do'],
        ]);
        $orderId = $pdo->lastInsertId();

        // Počiatočný záznam histórie
        $pdo->prepare("INSERT INTO stav_historia (objednavka_id, stav, poznamka) VALUES (?,?,?)")
            ->execute([$orderId, 'caka', 'Objednávka prijatá cez online formulár']);

        header('Location: /potvrdenie.php?ticket=' . urlencode($ticketId));
        exit;
    }
}

$prefill_zariadenie = e($_GET['zariadenie'] ?? '');
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[88px] pb-20 px-6 md:px-8 max-w-[1280px] mx-auto w-full">

  <div class="mb-10 reveal-up">
    <h1 class="text-5xl font-black text-on-background mb-2">Rezervovať opravu</h1>
    <p class="text-lg text-on-surface-variant">Vyplňte formulár a my sa ozveme do 2 hodín.</p>
  </div>

  <?php if ($chyba): ?>
    <div class="mb-6 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg flex items-center gap-2">
      <span class="material-symbols-outlined text-[20px]">error</span> <?= e($chyba) ?>
    </div>
  <?php endif; ?>

  <!-- Progress tracker -->
  <div class="flex items-center justify-between relative mb-10 max-w-lg reveal-up">
    <div class="absolute left-0 top-4 w-full h-[2px] bg-surface-container -z-10"></div>
    <div class="absolute left-0 top-4 w-1/3 h-[2px] bg-primary -z-10 transition-all duration-700" id="progress-bar"></div>
    <?php foreach ([['1','Zariadenie'],['2','Problém'],['3','Logistika']] as $i => [$n,$l]): ?>
      <div class="flex flex-col items-center gap-2">
        <div class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all
          <?= $i===0 ? 'bg-primary text-on-primary' : 'bg-surface-container-high border-2 border-outline-variant text-on-surface-variant' ?>">
          <?= $n ?>
        </div>
        <span class="text-xs font-semibold <?= $i===0 ? 'text-primary' : 'text-on-surface-variant' ?>"><?= $l ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="POST" id="rezervacia-form" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>

    <!-- ── Hlavný stĺpec ── -->
    <div class="lg:col-span-8 flex flex-col gap-6">

      <!-- Krok 1: Zariadenie -->
      <section class="form-section bg-surface-container-low border border-white/10 rounded-xl p-6 reveal-up" data-step="1">
        <h2 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">devices</span> Zariadenie
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="zariadenie_typ">Typ zariadenia *</label>
            <select id="zariadenie_typ" name="zariadenie_typ" required
                    class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors">
              <option value="">— Vyberte —</option>
              <?php foreach (['Smartfón','Notebook','Tablet','Herná konzola','Slúchadlá','Smartwatch','Iné'] as $typ): ?>
                <option value="<?= $typ ?>" <?= $prefill_zariadenie===$typ?'selected':'' ?>><?= $typ ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="zariadenie_model">Model zariadenia *</label>
            <input type="text" id="zariadenie_model" name="zariadenie_model" required
                   placeholder="napr. iPhone 14 Pro, MacBook Air M2"
                   class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors placeholder:text-on-surface-variant/50"/>
          </div>
        </div>
      </section>

      <!-- Krok 2: Problém -->
      <section class="form-section bg-surface-container border border-primary/40 rounded-xl p-6 shadow-[0_4px_24px_rgba(0,0,0,0.2)] reveal-up" data-step="2">
        <h2 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">build</span> Popis problému
        </h2>

        <div class="flex flex-col gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="problem_nazov">Stručný popis *</label>
            <input type="text" id="problem_nazov" name="problem_nazov" required
                   placeholder="napr. Prasknutý displej a rýchlo sa vybíja batéria"
                   class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors placeholder:text-on-surface-variant/50"/>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="problem_popis">Podrobný popis</label>
            <textarea id="problem_popis" name="problem_popis" rows="4"
                      placeholder="Popíšte kedy a ako nastala porucha, prípadné chybové hlásenia..."
                      class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors resize-none placeholder:text-on-surface-variant/50"></textarea>
          </div>

          <!-- Quick tags -->
          <div class="flex flex-wrap gap-2">
            <span class="text-xs text-on-surface-variant font-medium mt-1 mr-1">Rýchly výber:</span>
            <?php
            $tags = [
              ['water_drop','Poškodenie vodou','poskodenie_vodou'],
              ['battery_alert','Problém s batériou','problem_bateria'],
              ['broken_image','Prasknutý displej','prasknuty_displej'],
              ['power_off','Nezapína sa','nezapina'],
              ['wifi_off','Problém s Wi-Fi','problem_wifi'],
              ['volume_off','Problém so zvukom','problem_zvuk'],
            ];
            foreach ($tags as [$icon,$label,$value]): ?>
              <button type="button" onclick="selectTag(this,'<?= $value ?>')"
                      class="tag-btn flex items-center gap-1.5 bg-surface-container-high border border-white/10 hover:border-primary/50 text-on-surface-variant hover:text-primary px-3 py-1.5 rounded-full text-xs font-medium transition-all">
                <span class="material-symbols-outlined text-[14px]"><?= $icon ?></span><?= $label ?>
              </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="problem_kategoria" name="problem_kategoria"/>
        </div>
      </section>

      <!-- Krok 3: Logistika -->
      <section class="form-section bg-surface-container-low border border-white/10 rounded-xl p-6 reveal-up" data-step="3">
        <h2 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
          <span class="material-symbols-outlined text-on-surface-variant">local_shipping</span> Logistika
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
          <label class="doprava-option cursor-pointer border border-white/10 hover:border-primary/50 rounded-xl p-4 flex items-start gap-3 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/10">
            <input type="radio" name="doprava" value="osobne" checked class="mt-1 accent-[#72d6d8]"/>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">storefront</span>
                <span class="font-semibold text-on-surface">Osobné odovzdanie</span>
              </div>
              <p class="text-sm text-on-surface-variant">Prineste zariadenie priamo k nám na predajňu.</p>
              <p class="text-xs text-primary mt-1">Zadarmo</p>
            </div>
          </label>

          <label class="doprava-option cursor-pointer border border-white/10 hover:border-primary/50 rounded-xl p-4 flex items-start gap-3 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/10">
            <input type="radio" name="doprava" value="packeta" class="mt-1 accent-[#72d6d8]"/>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-cta text-[20px]">package_2</span>
                <span class="font-semibold text-on-surface">Packeta</span>
              </div>
              <p class="text-sm text-on-surface-variant">Odošlite cez Packeta box – pohodlne a bezpečne.</p>
              <p class="text-xs text-cta mt-1">+3,90 €</p>
            </div>
          </label>
        </div>

        <!-- Kontaktné údaje -->
        <h3 class="text-sm font-semibold text-on-surface-variant uppercase tracking-wider mb-4">Kontaktné údaje</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="meno">Meno a priezvisko *</label>
            <input type="text" id="meno" name="meno" required
                   value="<?= isLoggedIn() ? e($_SESSION['pouzivatel_meno'] ?? '') : '' ?>"
                   class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-sm font-medium text-on-surface" for="email">E-mail *</label>
            <input type="email" id="email" name="email" required
                   value="<?= isLoggedIn() ? e($_SESSION['pouzivatel_email'] ?? '') : '' ?>"
                   class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
          </div>
          <div class="flex flex-col gap-1.5 sm:col-span-2">
            <label class="text-sm font-medium text-on-surface" for="telefon">Telefónne číslo</label>
            <input type="tel" id="telefon" name="telefon"
                   placeholder="+421 9xx xxx xxx"
                   class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors placeholder:text-on-surface-variant/50"/>
          </div>
        </div>
      </section>
    </div>

    <!-- ── Sidebar ── -->
    <div class="lg:col-span-4">
      <div class="sticky top-[96px] bg-surface-container border border-white/10 rounded-xl p-6 flex flex-col gap-5 shadow-xl reveal-right">
        <h3 class="text-xl font-semibold text-on-surface border-b border-white/10 pb-4">Odhad ceny</h3>

        <div class="flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-on-surface-variant">Základná diagnostika</span>
            <span class="text-sm text-on-surface font-medium">25,00 €</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-on-surface-variant">Odhadované diely</span>
            <span class="text-sm text-on-surface font-medium">80 – 150 €</span>
          </div>
          <div class="flex justify-between items-center border-t border-white/10 pt-3 mt-1">
            <span class="text-sm font-semibold text-on-surface">Celkový odhad</span>
            <span class="text-xl font-black text-cta">105 – 175 €</span>
          </div>
        </div>

        <!-- Priorita -->
        <div class="bg-surface-container-high rounded-lg p-4 border border-white/10">
          <div class="flex justify-between items-center mb-3">
            <span class="text-sm text-on-surface-variant flex items-center gap-2">
              <span class="material-symbols-outlined text-[18px]">schedule</span> Štandardná fronta
            </span>
            <span class="text-sm font-medium text-on-surface">3 – 5 dní</span>
          </div>
          <label class="flex items-start gap-3 p-3 rounded-lg bg-primary/5 border border-primary/30 cursor-pointer hover:bg-primary/10 transition-colors">
            <input type="checkbox" name="priorita" class="mt-1 w-4 h-4 accent-[#72d6d8]"/>
            <div>
              <div class="flex justify-between items-center w-full">
                <span class="text-sm font-semibold text-on-surface">Prioritná oprava</span>
                <span class="text-sm font-bold text-cta ml-2">+35,00 €</span>
              </div>
              <span class="text-xs text-on-surface-variant mt-1 block">Preskočte frontu. Odhadovaný čas: 24 – 48 hod.</span>
            </div>
          </label>
        </div>

        <button type="submit"
                class="w-full bg-cta text-cta-dark font-bold py-4 rounded-xl hover:shadow-[0_0_20px_rgba(236,154,41,0.3)] transition-all flex items-center justify-center gap-2 text-lg">
          Odoslať rezerváciu
          <span class="material-symbols-outlined">send</span>
        </button>

        <p class="text-xs text-center text-on-surface-variant">
          Finálna cena bude potvrdená po fyzickej diagnostike zariadenia.
        </p>
      </div>
    </div>

  </form>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
function selectTag(btn, value) {
  document.querySelectorAll('.tag-btn').forEach(b => {
    b.classList.remove('bg-primary/20','border-primary','text-primary');
    b.classList.add('bg-surface-container-high','border-white/10','text-on-surface-variant');
  });
  btn.classList.add('bg-primary/20','border-primary','text-primary');
  btn.classList.remove('bg-surface-container-high','border-white/10','text-on-surface-variant');
  document.getElementById('problem_kategoria').value = value;
}
</script>
