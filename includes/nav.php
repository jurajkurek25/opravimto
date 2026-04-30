<?php
$activeNav = $activeNav ?? '';
$navLinks = [
    'opravovna' => ['href' => '/index.php',     'label' => 'Opravovňa'],
    'stav'      => ['href' => '/stav.php',       'label' => 'Stav opravy'],
    'cennik'    => ['href' => '/cennik.php',     'label' => 'Cenník'],
    'o-nas'     => ['href' => '/o-nas.php',      'label' => 'O nás'],
];
?>
<nav id="main-nav" class="bg-background/80 backdrop-blur-md fixed top-0 w-full z-50 border-b border-white/10 transition-all duration-300">
  <div class="flex justify-between items-center px-6 md:px-8 py-4 max-w-[1280px] mx-auto">
    <!-- Logo -->
    <a href="/index.php" class="flex items-center gap-2 text-2xl font-black tracking-tighter text-primary hover:text-primary-fixed transition-colors">
      <span class="material-symbols-outlined text-cta" style="font-size:28px">build_circle</span>
      opravimto.sk
    </a>

    <!-- Desktop Nav -->
    <div class="hidden md:flex gap-8 items-center">
      <?php foreach ($navLinks as $key => $link): ?>
        <a href="<?= $link['href'] ?>"
           class="text-sm font-medium tracking-tight transition-colors <?= $activeNav === $key ? 'text-primary border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary' ?>">
          <?= $link['label'] ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-3 md:gap-4">
      <!-- Search (desktop) -->
      <div class="hidden lg:flex relative text-on-surface-variant focus-within:text-primary transition-colors">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px]">search</span>
        <input type="text" placeholder="Hľadať opravu..."
               class="bg-surface-container border border-white/10 rounded-full py-2 pl-9 pr-4 text-sm text-on-surface focus:outline-none focus:border-primary transition-colors w-44 placeholder:text-on-surface-variant/50"/>
      </div>

      <?php if (isLoggedIn()): ?>
        <a href="/moj-ucet.php" class="text-on-surface-variant hover:text-primary transition-colors flex items-center">
          <span class="material-symbols-outlined">account_circle</span>
        </a>
        <a href="/odhlasenie.php" class="text-on-surface-variant hover:text-primary transition-colors flex items-center" title="Odhlásiť sa">
          <span class="material-symbols-outlined">logout</span>
        </a>
      <?php else: ?>
        <a href="/prihlasenie.php" class="text-on-surface-variant hover:text-primary transition-colors flex items-center" title="Prihlásiť sa">
          <span class="material-symbols-outlined">person</span>
        </a>
      <?php endif; ?>

      <a href="/rezervacia.php"
         class="bg-cta text-cta-dark font-bold text-sm px-5 py-2 rounded-lg hover:shadow-[0_0_18px_rgba(236,154,41,0.35)] transition-all whitespace-nowrap">
        Rezervovať opravu
      </a>

      <!-- Mobile hamburger -->
      <button id="mobile-menu-btn" class="md:hidden text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined">menu</span>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-surface-container-low border-t border-white/10 px-6 pb-4">
    <?php foreach ($navLinks as $key => $link): ?>
      <a href="<?= $link['href'] ?>"
         class="block py-3 text-sm font-medium border-b border-white/5 transition-colors <?= $activeNav === $key ? 'text-primary' : 'text-on-surface-variant hover:text-primary' ?>">
        <?= $link['label'] ?>
      </a>
    <?php endforeach; ?>
    <a href="/rezervacia.php" class="block mt-4 bg-cta text-cta-dark font-bold text-sm text-center px-4 py-3 rounded-lg">
      Rezervovať opravu
    </a>
  </div>
</nav>
