<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) { header('Location: /moj-ucet.php'); exit; }

$pageTitle = 'Prihlásenie';
$activeNav = '';
$tab = $_GET['tab'] ?? 'prihlasenie';
$chyba = '';
$redirect = $_GET['redirect'] ?? '/moj-ucet.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $akcia = $_POST['akcia'] ?? '';

    if ($akcia === 'prihlasenie') {
        $email = trim($_POST['email'] ?? '');
        $heslo = $_POST['heslo'] ?? '';
        $stmt = getPDO()->prepare("SELECT * FROM pouzivatelia WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($heslo, $u['heslo'])) {
            $_SESSION['pouzivatel_id']    = $u['id'];
            $_SESSION['pouzivatel_meno']  = $u['meno'];
            $_SESSION['pouzivatel_email'] = $u['email'];
            header('Location: ' . $redirect);
            exit;
        }
        $chyba = 'Nesprávny e-mail alebo heslo.';
        $tab = 'prihlasenie';

    } elseif ($akcia === 'registracia') {
        $meno   = trim($_POST['meno'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $heslo  = $_POST['heslo'] ?? '';
        $heslo2 = $_POST['heslo2'] ?? '';

        if (!$meno || !$email || !$heslo) { $chyba = 'Vyplňte všetky polia.'; $tab='registracia'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $chyba = 'Neplatný e-mail.'; $tab='registracia'; }
        elseif (strlen($heslo) < 6) { $chyba = 'Heslo musí mať aspoň 6 znakov.'; $tab='registracia'; }
        elseif ($heslo !== $heslo2) { $chyba = 'Heslá sa nezhodujú.'; $tab='registracia'; }
        else {
            try {
                $hash = password_hash($heslo, PASSWORD_BCRYPT, ['cost'=>12]);
                getPDO()->prepare("INSERT INTO pouzivatelia (meno, email, heslo) VALUES (?,?,?)")
                    ->execute([$meno, $email, $hash]);
                $id = getPDO()->lastInsertId();
                $_SESSION['pouzivatel_id']    = $id;
                $_SESSION['pouzivatel_meno']  = $meno;
                $_SESSION['pouzivatel_email'] = $email;
                header('Location: ' . $redirect);
                exit;
            } catch (PDOException $e) {
                $chyba = 'E-mail je už zaregistrovaný.';
                $tab = 'registracia';
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/head.php'; ?>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<main class="flex-grow pt-[88px] pb-20 flex items-center justify-center px-6">
  <div class="w-full max-w-md reveal-up">

    <div class="text-center mb-8">
      <div class="w-16 h-16 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4 border border-primary/20">
        <span class="material-symbols-outlined text-primary text-3xl">account_circle</span>
      </div>
      <h1 class="text-3xl font-black text-on-background">Váš účet</h1>
      <p class="text-on-surface-variant text-sm mt-1">Prihlásením sledujte stav opravy kedykoľvek</p>
    </div>

    <!-- Tab switcher -->
    <div class="flex rounded-xl bg-surface-container p-1 mb-6 border border-white/10">
      <a href="?tab=prihlasenie&redirect=<?= urlencode($redirect) ?>"
         class="flex-1 text-center py-2.5 text-sm font-semibold rounded-lg transition-all <?= $tab==='prihlasenie' ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:text-primary' ?>">
        Prihlásenie
      </a>
      <a href="?tab=registracia&redirect=<?= urlencode($redirect) ?>"
         class="flex-1 text-center py-2.5 text-sm font-semibold rounded-lg transition-all <?= $tab==='registracia' ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:text-primary' ?>">
        Registrácia
      </a>
    </div>

    <?php if ($chyba): ?>
      <div class="mb-4 bg-error/10 border border-error/30 text-error px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">error</span> <?= e($chyba) ?>
      </div>
    <?php endif; ?>

    <?php if ($tab === 'prihlasenie'): ?>
    <form method="POST" class="bg-surface-container border border-white/10 rounded-xl p-6 flex flex-col gap-4">
      <input type="hidden" name="akcia" value="prihlasenie"/>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="email">E-mail</label>
        <input type="email" id="email" name="email" required autocomplete="email"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="heslo">Heslo</label>
        <input type="password" id="heslo" name="heslo" required autocomplete="current-password"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <button type="submit" class="w-full bg-cta text-cta-dark font-bold py-3.5 rounded-xl hover:shadow-[0_0_20px_rgba(236,154,41,0.3)] transition-all mt-2">
        Prihlásiť sa
      </button>
      <p class="text-xs text-center text-on-surface-variant">Nemáte účet? <a href="?tab=registracia" class="text-primary hover:underline">Zaregistrujte sa</a></p>
    </form>

    <?php else: ?>
    <form method="POST" class="bg-surface-container border border-white/10 rounded-xl p-6 flex flex-col gap-4">
      <input type="hidden" name="akcia" value="registracia"/>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="meno">Meno a priezvisko</label>
        <input type="text" id="meno" name="meno" required autocomplete="name"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="reg-email">E-mail</label>
        <input type="email" id="reg-email" name="email" required autocomplete="email"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="reg-heslo">Heslo (min. 6 znakov)</label>
        <input type="password" id="reg-heslo" name="heslo" required autocomplete="new-password"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium text-on-surface" for="heslo2">Zopakujte heslo</label>
        <input type="password" id="heslo2" name="heslo2" required autocomplete="new-password"
               class="bg-surface-container-high border border-outline/30 rounded-lg px-4 py-3 text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"/>
      </div>
      <button type="submit" class="w-full bg-cta text-cta-dark font-bold py-3.5 rounded-xl hover:shadow-[0_0_20px_rgba(236,154,41,0.3)] transition-all mt-2">
        Vytvoriť účet
      </button>
      <p class="text-xs text-center text-on-surface-variant">Máte účet? <a href="?tab=prihlasenie" class="text-primary hover:underline">Prihláste sa</a></p>
    </form>
    <?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
