<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Domov';
$activeNav = 'opravovna';
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[72px]">

  <!-- ── Hero ── -->
  <section class="relative max-w-[1280px] mx-auto px-6 md:px-8 py-20 md:py-32 flex flex-col md:flex-row items-center gap-12 overflow-hidden">
    <!-- Background glow -->
    <div class="hero-glow absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>

    <!-- Canvas particles -->
    <canvas id="hero-canvas" class="absolute inset-0 w-full h-full pointer-events-none opacity-30"></canvas>

    <!-- Text -->
    <div class="flex-1 flex flex-col gap-6 z-10 reveal-up">
      <div class="inline-flex items-center gap-2 bg-surface-container border border-white/10 px-4 py-2 rounded-full w-fit">
        <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
        <span class="text-xs font-semibold tracking-widest text-primary uppercase">Profesionálna klinika elektroniky</span>
      </div>

      <h1 class="text-5xl md:text-6xl font-black leading-tight tracking-tight text-on-background">
        Precízne opravy<br/>
        <span class="text-primary">vašich zariadení.</span>
      </h1>

      <p class="text-lg text-on-surface-variant max-w-xl leading-relaxed">
        Rýchly, spoľahlivý a transparentný servis. Diagnostikujeme a opravíme hardvérové
        problémy s inžinierskou presnosťou — aby ste sa čo najskôr vrátili online.
      </p>

      <div class="flex flex-col sm:flex-row gap-4 mt-2">
        <a href="/rezervacia.php"
           class="btn-primary inline-flex items-center justify-center gap-2 bg-cta text-cta-dark font-bold px-8 py-4 rounded-lg hover:shadow-[0_0_20px_rgba(236,154,41,0.3)] transition-all">
          Rezervovať opravu
          <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </a>
        <a href="/stav.php"
           class="inline-flex items-center justify-center gap-2 border border-primary text-primary font-bold px-8 py-4 rounded-lg hover:bg-primary/10 transition-all">
          Skontrolovať stav
          <span class="material-symbols-outlined text-[20px]">search</span>
        </a>
      </div>

      <!-- Trust badges -->
      <div class="flex flex-wrap gap-4 mt-2">
        <div class="flex items-center gap-1.5 text-on-surface-variant text-sm">
          <span class="material-symbols-outlined text-primary text-[18px]">schedule</span> Oprava do 24 hodín
        </div>
        <div class="flex items-center gap-1.5 text-on-surface-variant text-sm">
          <span class="material-symbols-outlined text-primary text-[18px]">workspace_premium</span> 12-mesačná záruka
        </div>
        <div class="flex items-center gap-1.5 text-on-surface-variant text-sm">
          <span class="material-symbols-outlined text-primary text-[18px]">payments</span> Platíte až po oprave
        </div>
      </div>
    </div>

    <!-- Hero visual -->
    <div class="flex-1 relative w-full aspect-[4/3] rounded-xl overflow-hidden border border-white/10 bg-surface-container shadow-2xl z-10 reveal-right">
      <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDC3WmH492YXKdLsLu7G_RBW7eHWNdXuIiVvd0Id4NSMfDogQgnK8nIiJZ8o8jGXs6Z9iAAqgpIdmBxlWVZEmyXRc2v9I7Bz0x0UIGnm0iACi4GVQvKMoaXOQ7e09WJXu7wO3_1W0xKy6TNdfjpCBwqec2jk5II_J5ju7JNeUPOXUGHLFGvK3Xgesp_u-2IdVoSW2cjanCEYSXfbQmk6FMfY5c6CH12eXkuTTyIPNgtu3PLW6src18y5Nsv37zkj4jOon4c1Ep5QJhQ"
           alt="Oprava elektroniky" class="w-full h-full object-cover opacity-80 mix-blend-luminosity"/>
      <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>

      <!-- Floating card -->
      <div class="floating-card absolute bottom-6 left-6 bg-surface-container-high border border-white/10 p-4 rounded-lg backdrop-blur-md flex items-center gap-4 shadow-xl">
        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center border border-primary/40">
          <span class="material-symbols-outlined text-primary text-[20px]">memory</span>
        </div>
        <div>
          <div class="text-xs text-on-surface-variant">Diagnostika dokončená</div>
          <div class="text-sm font-semibold text-primary">Systém pripravený</div>
        </div>
      </div>

      <!-- Live counter -->
      <div class="absolute top-6 right-6 bg-surface-container-high border border-white/10 p-3 rounded-lg backdrop-blur-md text-center">
        <div class="text-2xl font-black text-cta counter" data-target="248">0</div>
        <div class="text-xs text-on-surface-variant">opráv tento mesiac</div>
      </div>
    </div>
  </section>

  <!-- ── Device Quick Search ── -->
  <section class="bg-surface-container-low border-y border-white/10">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 py-10 flex flex-col md:flex-row items-center justify-between gap-6">
      <h2 class="text-2xl font-semibold text-on-surface whitespace-nowrap">Čo chcete opraviť?</h2>
      <div class="flex-1 max-w-2xl relative w-full">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
        <input type="text" id="quick-search"
               placeholder="napr. iPhone 13 displej, MacBook batéria..."
               class="w-full bg-surface-container-high border border-white/10 rounded-lg py-4 pl-12 pr-28 text-on-surface focus:outline-none focus:border-primary transition-colors placeholder:text-on-surface-variant/50"/>
        <button onclick="window.location.href='/rezervacia.php?q='+encodeURIComponent(document.getElementById('quick-search').value)"
                class="absolute right-2 top-1/2 -translate-y-1/2 bg-cta text-cta-dark font-bold text-sm px-4 py-2.5 rounded-md hover:brightness-110 transition-all">
          Hľadať
        </button>
      </div>
    </div>

    <!-- Device type chips -->
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 pb-8 flex flex-wrap gap-3">
      <?php
      $devices = [
        ['smartphone','Smartfón'],['laptop_mac','Notebook'],['tablet_mac','Tablet'],
        ['sports_esports','Konzola'],['headphones','Slúchadlá'],['watch','Smartwatch'],
      ];
      foreach ($devices as [$icon, $label]): ?>
        <a href="/rezervacia.php?zariadenie=<?= urlencode($label) ?>"
           class="device-chip flex items-center gap-2 bg-surface-container border border-white/10 hover:border-primary/50 hover:bg-primary/10 text-on-surface-variant hover:text-primary px-4 py-2 rounded-full text-sm font-medium transition-all cursor-pointer">
          <span class="material-symbols-outlined text-[18px]"><?= $icon ?></span>
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ── Benefits Bento Grid ── -->
  <section class="max-w-[1280px] mx-auto px-6 md:px-8 py-20">
    <div class="text-center mb-12 reveal-up">
      <h2 class="text-4xl font-bold text-on-background mb-3">Navrhnuté pre spoľahlivosť</h2>
      <p class="text-on-surface-variant max-w-2xl mx-auto">Náš proces je navrhnutý tak, aby minimalizoval prestoje a maximalizoval transparentnosť.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Big card -->
      <div class="bento-card md:col-span-2 bg-surface-container-high border border-white/10 rounded-xl p-8 flex flex-col justify-between hover:border-primary/30 hover:bg-surface-container-highest transition-all group reveal-up">
        <div>
          <span class="material-symbols-outlined text-cta text-5xl mb-5 block group-hover:scale-110 transition-transform">speed</span>
          <h3 class="text-2xl font-semibold text-on-surface mb-2">Expresné vybavenie</h3>
          <p class="text-on-surface-variant leading-relaxed">Väčšina bežných opráv je hotová do 24 hodín. Sledujte stav svojho zariadenia v reálnom čase cez náš presný portál.</p>
        </div>
        <div class="flex gap-2 mt-6">
          <span class="px-3 py-1 bg-primary/10 border border-primary/30 rounded-full text-xs font-semibold text-primary">Prioritná fronta</span>
          <span class="px-3 py-1 bg-primary/10 border border-primary/30 rounded-full text-xs font-semibold text-primary">Live sledovanie</span>
        </div>
      </div>

      <div class="bento-card bg-surface-container-high border border-white/10 rounded-xl p-8 hover:border-primary/30 transition-all group reveal-up" style="--delay:100ms">
        <span class="material-symbols-outlined text-primary text-5xl mb-5 block group-hover:scale-110 transition-transform">verified_user</span>
        <h3 class="text-2xl font-semibold text-on-surface mb-2">Certifikovaní technici</h3>
        <p class="text-on-surface-variant leading-relaxed">Tím odborníkov vyškolených v mikrosoldávaní a moderných diagnostických postupoch.</p>
      </div>

      <div class="bento-card bg-surface-container-high border border-white/10 rounded-xl p-8 hover:border-primary/30 transition-all group reveal-up" style="--delay:150ms">
        <span class="material-symbols-outlined text-primary text-5xl mb-5 block group-hover:scale-110 transition-transform">payments</span>
        <h3 class="text-2xl font-semibold text-on-surface mb-2">Transparentné ceny</h3>
        <p class="text-on-surface-variant leading-relaxed">Žiadne skryté poplatky. Finálnu cenu schválite vy pred začatím akéhokoľvek zásahu do zariadenia.</p>
      </div>

      <div class="bento-card md:col-span-2 bg-surface-container-high border border-white/10 rounded-xl p-8 hover:border-primary/30 transition-all group reveal-up" style="--delay:200ms">
        <span class="material-symbols-outlined text-primary text-5xl mb-5 block group-hover:scale-110 transition-transform">inventory_2</span>
        <h3 class="text-2xl font-semibold text-on-surface mb-2">Prémiové náhradné diely</h3>
        <p class="text-on-surface-variant leading-relaxed max-w-md">Používame len originálne alebo ekvivalentné vysokokvalitatívne komponenty, ktoré zaručujú dlhodobú spoľahlivosť.</p>
      </div>
    </div>
  </section>

  <!-- ── Stats ── -->
  <section class="bg-surface-container-low border-y border-white/10 py-16">
    <div class="max-w-[1280px] mx-auto px-6 md:px-8 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
      <?php
      $stats = [
        ['248+','Opráv mesačne','speed'],
        ['98%','Spokojnosť','thumb_up'],
        ['24h','Priemerná doba','schedule'],
        ['12M','Záruka na opravu','workspace_premium'],
      ];
      foreach ($stats as [$value, $label, $icon]): ?>
        <div class="reveal-up stats-item">
          <span class="material-symbols-outlined text-primary text-4xl mb-3 block"><?= $icon ?></span>
          <div class="text-4xl font-black text-on-surface mb-1"><?= $value ?></div>
          <div class="text-sm text-on-surface-variant"><?= $label ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ── CTA ── -->
  <section class="max-w-[1280px] mx-auto px-6 md:px-8 py-20 text-center reveal-up">
    <h2 class="text-4xl font-bold text-on-background mb-4">Vaše zariadenie si zaslúži to najlepšie.</h2>
    <p class="text-on-surface-variant mb-8 max-w-xl mx-auto">Rezervujte opravu online za 2 minúty. Naši technici sa postarajú o zvyšok.</p>
    <a href="/rezervacia.php"
       class="inline-flex items-center gap-3 bg-cta text-cta-dark font-bold px-10 py-5 rounded-xl text-lg hover:shadow-[0_0_30px_rgba(236,154,41,0.4)] transition-all pulse-glow">
      Začať rezerváciu
      <span class="material-symbols-outlined">arrow_forward</span>
    </a>
  </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
