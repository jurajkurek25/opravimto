<?php
function genTicketId(): string {
    return 'OPR-' . strtoupper(substr(md5(uniqid('', true)), 0, 4)) . '-' . strtoupper(substr(md5(microtime()), 0, 2));
}

function stavLabel(string $stav): array {
    return match($stav) {
        'caka'        => ['Čaká na odovzdanie', 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400'],
        'diagnostika' => ['Diagnostika',         'bg-[#ffb964]/10 border-[#ffb964]/30 text-[#ffb964]'],
        'oprava'      => ['Prebieha oprava',     'bg-primary/10 border-primary/30 text-primary'],
        'testovanie'  => ['Testovanie',           'bg-blue-500/10 border-blue-500/30 text-blue-400'],
        'hotovo'      => ['Pripravené',           'bg-green-500/10 border-green-500/30 text-green-400'],
        'zrusena'     => ['Zrušená',              'bg-error/10 border-error/30 text-error'],
        default       => ['Neznámy',              'bg-white/10 border-white/20 text-on-surface-variant'],
    };
}

function stavProgress(string $stav): int {
    return match($stav) {
        'caka'        => 0,
        'diagnostika' => 1,
        'oprava'      => 2,
        'testovanie'  => 3,
        'hotovo'      => 4,
        default       => 0,
    };
}

function zariadeniIkona(string $typ): string {
    return match(mb_strtolower($typ)) {
        'smartfón', 'telefón', 'mobil' => 'smartphone',
        'notebook', 'laptop'            => 'laptop_mac',
        'tablet'                        => 'tablet_mac',
        'herná konzola', 'konzola'      => 'sports_esports',
        'slúchadlá'                     => 'headphones',
        'hodinky', 'smartwatch'         => 'watch',
        default                         => 'devices',
    };
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isLoggedIn(): bool {
    return isset($_SESSION['pouzivatel_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /prihlasenie.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die(json_encode(['chyba' => 'Neplatný CSRF token.']));
    }
}
