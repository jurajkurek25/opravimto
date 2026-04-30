<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Môj účet';
$activeNav = 'stav';

$pdo = getPDO();
$userId = $_SESSION['pouzivatel_id'];

// Používateľské údaje
$user = $pdo->prepare("SELECT * FROM pouzivatelia WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();

// Objednávky
$objednavky = $pdo->prepare("SELECT * FROM objednavky WHERE pouzivatel_id = ? ORDER BY created_at DESC");
$objednavky->execute([$userId]);
$objednavky = $objednavky->fetchAll();

// Aktívna (posledná otvorená)
$aktivna = null;
foreach ($objednavky as $o) {
    if ($o['stav'] !== 'hotovo' && $o['stav'] !== 'zrusena') {
        $aktivna = $o;
        break;
    }
}

// Správy pre aktívnu
$spravy = [];
if ($aktivna) {
    $s = $pdo->prepare("SELECT * FROM spravy WHERE objednavka_id = ? ORDER BY created_at ASC");
    $s->execute([$aktivna['id']]);
    $spravy = $s->fetchAll();
}

$stavLabels = [
    'caka'        => 'Čaká na odovzdanie',
    'diagnostika' => 'Diagnostika',
    'oprava'      => 'Prebieha oprava',
    'testovanie'  => 'Testovanie',
    'hotovo'      => 'Pripravené',
    'zrusena'     => 'Zrušená',
];
$stavProgress = ['caka'=>0,'diagnostika'=>1,'oprava'=>2,'testovanie'=>3,'hotovo'=>4,'zrusena'=>0];
$progressFazy = ['Prijaté','Diagnostika','Oprava','Testovanie','Pripravené'];
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[88px] pb-20 px-6 md:px-8 max-w-[1280px] mx-auto w-full">
  <div class="mb-8 reveal-up">
    <h1 class="text-4xl font-black text-on-background">Môj účet</h1>
    <p class="text-on-surface-variant mt-1">Vitajte späť, <strong><?= e($user['meno']) ?></strong></p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

    <!-- Aktívna oprava -->
    <?php if ($aktivna): ?>
    <div class="md:col-span-8 bg-surface-container border border-outline-variant rounded-xl p-6 relative overflow-hidden reveal-up">
      <?php if ($aktivna['priorita']): ?>
        <div class="absolute top-4 right-4 bg-error/10 border border-error text-error text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 z-10">
          <span class="material-symbols-outlined text-[14px]">warning</span> Priorita: Vysoká
        </div>
      <?php endif; ?>

      <div class="flex gap-5 items-start mb-8">
        <div class="w-24 h-24 rounded-lg bg-surface-variant border border-outline-variant overflow-hidden flex-shrink-0">
          <div class="w-full h-full flex items-center justify-center bg-surface-container">
            <span class="material-symbols-outlined text-primary text-5xl"><?= zariadeniIkona($aktivna['zariadenie_typ']) ?></span>
          </div>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-on-surface"><?= e($aktivna['zariadenie_model']) ?></h2>
          <p class="text-on-surface-variant text-sm">Číslo tiketu: <span class="font-mono text-primary"><?= e($aktivna['ticket_id']) ?></span></p>
          <span class="inline-block mt-2 text-xs font-semibold px-3 py-1 rounded <?= stavLabel($aktivna['stav'])[1] ?>"><?= stavLabel($aktivna['stav'])[0] ?></span>
        </div>
      </div>

      <!-- Progress tracker -->
      <div class="px-4">
        <div class="flex justify-between mb-2">
          <?php foreach ($progressFazy as $i => $faza):
            $step = $stavProgress[$aktivna['stav']] ?? 0; ?>
            <span class="text-xs font-semibold <?= $i <= $step ? 'text-primary' : 'text-on-surface-variant' ?>"><?= $faza ?></span>
          <?php endforeach; ?>
        </div>
        <div class="relative flex items-center justify-between">
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-[2px] bg-outline-variant -z-10"></div>
          <?php
          $step = $stavProgress[$aktivna['stav']] ?? 0;
          foreach ($progressFazy as $i => $faza): ?>
            <div class="w-6 h-6 rounded-full flex items-center justify-center transition-all
              <?php if ($i < $step) echo 'bg-primary shadow-[0_0_10px_rgba(114,214,216,0.3)]';
                    elseif ($i === $step) echo 'bg-secondary-container border-2 border-secondary animate-pulse shadow-[0_0_15px_rgba(204,128,4,0.4)]';
                    else echo 'bg-surface-variant border border-outline'; ?>">
              <?php if ($i < $step): ?>
                <span class="material-symbols-outlined text-on-primary text-[14px]">check</span>
              <?php elseif ($i === $step): ?>
                <span class="w-2 h-2 rounded-full bg-secondary"></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ($aktivna['poznamka_technika']): ?>
        <div class="mt-4 bg-surface-variant/50 p-3 rounded text-center border border-outline-variant/50">
          <p class="text-sm text-on-surface"><?= e($aktivna['poznamka_technika']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chat s technikom -->
    <div class="md:col-span-4 bg-surface-container-high border border-outline-variant rounded-xl p-5 flex flex-col reveal-right">
      <div class="flex items-center gap-3 mb-5 border-b border-outline-variant pb-4">
        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
          <span class="material-symbols-outlined text-primary">support_agent</span>
        </div>
        <div>
          <div class="font-semibold text-on-surface text-sm">Technická podpora</div>
          <div class="text-xs text-primary flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span> Online
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto mb-4 flex flex-col gap-3 max-h-64 pr-1" id="chat-messages">
        <?php foreach ($spravy as $sprava): ?>
          <div class="<?= $sprava['od_admina'] ? 'self-start bg-surface-container-lowest border border-outline-variant rounded-xl rounded-tl-none' : 'self-end bg-primary/10 border border-primary/20 rounded-xl rounded-tr-none' ?> p-3 max-w-[90%]">
            <p class="text-sm text-on-surface"><?= nl2br(e($sprava['sprava'])) ?></p>
            <span class="text-xs text-on-surface-variant mt-1 block text-right">
              <?= date('H:i', strtotime($sprava['created_at'])) ?>
            </span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($spravy)): ?>
          <p class="text-sm text-on-surface-variant text-center mt-4">Zatiaľ žiadne správy.</p>
        <?php endif; ?>
      </div>

      <form action="/api/send-message.php" method="POST" id="chat-form" class="flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"/>
        <input type="hidden" name="objednavka_id" value="<?= $aktivna['id'] ?>"/>
        <input type="text" name="sprava" placeholder="Napíšte správu..."
               class="flex-1 bg-surface-container border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary transition-colors"/>
        <button type="submit" class="bg-primary text-on-primary px-4 rounded-lg hover:bg-primary-fixed transition-colors flex items-center justify-center">
          <span class="material-symbols-outlined text-[18px]">send</span>
        </button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Nedávne objednávky -->
    <div class="md:col-span-<?= $aktivna ? '8' : '12' ?> bg-surface-container border border-outline-variant rounded-xl p-6 reveal-up">
      <div class="flex justify-between items-center mb-5">
        <h3 class="text-xl font-semibold text-on-surface">Nedávne objednávky</h3>
        <a href="/rezervacia.php" class="text-sm text-primary hover:text-primary-fixed transition-colors flex items-center gap-1">
          Nová objednávka <span class="material-symbols-outlined text-[16px]">add</span>
        </a>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-outline-variant text-xs font-semibold uppercase tracking-wider text-on-surface-variant">
              <th class="pb-3">Číslo</th>
              <th class="pb-3">Zariadenie</th>
              <th class="pb-3 hidden sm:table-cell">Dátum</th>
              <th class="pb-3">Stav</th>
              <th class="pb-3 text-right">Akcia</th>
            </tr>
          </thead>
          <tbody class="text-sm divide-y divide-outline-variant/30">
            <?php foreach ($objednavky as $o):
              [$label, $css] = stavLabel($o['stav']); ?>
              <tr class="hover:bg-surface-container-high transition-colors">
                <td class="py-3 font-mono text-primary text-xs"><?= e($o['ticket_id']) ?></td>
                <td class="py-3 text-on-surface"><?= e($o['zariadenie_model']) ?></td>
                <td class="py-3 text-on-surface-variant hidden sm:table-cell"><?= date('d.m.Y', strtotime($o['created_at'])) ?></td>
                <td class="py-3"><span class="px-2 py-1 rounded text-xs font-semibold border <?= $css ?>"><?= $label ?></span></td>
                <td class="py-3 text-right">
                  <a href="/stav.php?ticket=<?= urlencode($o['ticket_id']) ?>" class="text-primary hover:underline text-xs">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($objednavky)): ?>
              <tr><td colspan="5" class="py-8 text-center text-on-surface-variant">Zatiaľ žiadne objednávky.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Nastavenia účtu -->
    <div class="md:col-span-4 bg-surface-container border border-outline-variant rounded-xl p-6 flex flex-col reveal-right">
      <h3 class="text-xl font-semibold text-on-surface mb-5 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary">account_circle</span> Nastavenia účtu
      </h3>
      <div class="flex flex-col gap-3 flex-1">
        <?php foreach ([
          ['Meno a priezvisko', $user['meno'], 'edit'],
          ['E-mail', $user['email'], 'edit'],
          ['Telefón', $user['telefon'] ?: '—', 'edit'],
        ] as [$l,$v,$i]): ?>
          <div class="bg-surface-container-low p-3 rounded-lg border border-outline-variant/50 flex justify-between items-center">
            <div>
              <div class="text-xs text-on-surface-variant"><?= $l ?></div>
              <div class="text-sm font-medium text-on-surface mt-0.5"><?= e($v) ?></div>
            </div>
            <span class="material-symbols-outlined text-on-surface-variant text-[18px]"><?= $i ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <a href="/odhlasenie.php" class="mt-5 border border-error/40 text-error hover:bg-error/10 font-medium py-2.5 rounded-lg text-center transition-colors text-sm flex items-center justify-center gap-2">
        <span class="material-symbols-outlined text-[18px]">logout</span> Odhlásiť sa
      </a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<script>
// Scroll chat to bottom
const chat = document.getElementById('chat-messages');
if (chat) chat.scrollTop = chat.scrollHeight;

// Ajax chat submit
document.getElementById('chat-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const input = form.querySelector('input[name="sprava"]');
  if (!input.value.trim()) return;
  const res = await fetch(form.action, { method:'POST', body: new FormData(form) });
  const data = await res.json();
  if (data.ok) {
    const div = document.createElement('div');
    div.className = 'self-end bg-primary/10 border border-primary/20 rounded-xl rounded-tr-none p-3 max-w-[90%]';
    div.innerHTML = `<p class="text-sm text-on-surface">${data.sprava}</p><span class="text-xs text-on-surface-variant mt-1 block text-right">${data.cas}</span>`;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
    input.value = '';
  }
});
</script>
