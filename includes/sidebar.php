<?php
$current = basename($_SERVER['PHP_SELF']);
$role    = $_SESSION['role'] ?? '';
$nama    = $_SESSION['nama_lengkap'] ?? 'Pengguna';

$icons = [
    'dashboard' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
    'tag'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    'cap'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
    'building'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 8h.01M15 8h.01M9 12h.01M15 12h.01"/><path d="M10 21v-4h4v4"/></svg>',
    'user'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'users'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'check'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    'plus'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
    'settings'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
];

$menuUtama = [
    'admin' => [
        ['index.php',        'Dashboard',     $icons['dashboard']],
        ['kategori.php',     'Kategori',      $icons['tag']],
        ['kelas.php',        'Kelas',         $icons['cap']],
        ['jurusan.php',      'Jurusan',       $icons['building']],
        ['guru.php',         'Data Guru',     $icons['user']],
        ['pengguna.php',     'Semua Pengguna', $icons['users']],
    ],
    'guru' => [
        ['index.php',        'Dashboard',     $icons['dashboard']],
        ['verifikasi.php',   'Verifikasi',    $icons['check']],
        ['siswa.php',        'Data Siswa',    $icons['users']],
    ],
    'siswa' => [
        ['index.php',          'Dashboard',        $icons['dashboard']],
        ['tambah_laporan.php', 'Laporkan Sarana',  $icons['plus']],
    ],
];

$roleLabel = ['admin' => 'Administrator', 'guru' => 'Wali Kelas', 'siswa' => 'Siswa'][$role] ?? ucfirst($role);
?>
<button class="menu-fab" id="menuFab" type="button" aria-label="Buka menu">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>

<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <a class="brand" href="index.php">
            <span class="brand-mark">S</span>
            <span class="brand-text">SaranaCare<span class="brand-dot">.</span></span>
        </a>
    </div>

    <span class="side-section">Menu Utama</span>
    <nav class="side-nav">
        <?php foreach ($menuUtama[$role] ?? [] as [$href, $label, $icon]): ?>
            <a href="<?= $href ?>" class="side-link <?= $current === $href ? 'active' : '' ?>">
                <?= $icon ?>
                <span><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <span class="side-section">Akun</span>
    <nav class="side-nav side-nav--foot">
        <a href="profil.php" class="side-link <?= $current === 'profil.php' ? 'active' : '' ?>">
            <?= $icons['settings'] ?>
            <span>Profil Saya</span>
        </a>
        <a href="../auth/logout.php" class="side-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
            <span>Keluar</span>
        </a>
    </nav>

    <div class="side-user">
        <span class="avatar"><?= htmlspecialchars(substr($nama, 0, 1)) ?></span>
        <span class="u-meta">
            <strong><?= htmlspecialchars($nama) ?></strong>
            <small><?= htmlspecialchars($roleLabel) ?></small>
        </span>
    </div>
</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<script>
(function () {
    var sidebar  = document.getElementById('appSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var btn      = document.getElementById('menuFab');

    function toggle(force) {
        var open = typeof force === 'boolean' ? force : !sidebar.classList.contains('open');
        sidebar.classList.toggle('open', open);
        backdrop.classList.toggle('show', open);
    }

    btn.addEventListener('click', function () { toggle(); });
    backdrop.addEventListener('click', function () { toggle(false); });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) toggle(false);
    });
})();
</script>
