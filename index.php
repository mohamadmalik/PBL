<?php
session_start();

// Wajib login
if (empty($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Koneksi database
$pdo = new PDO("mysql:host=localhost;dbname=pbl semua;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT nama, email, role FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

require_once 'helpers.php';
$nama    = ambilNama($user['email']);
$role    = ucfirst(tentukanRole($user['email']) ?? 'user');
$inisial = strtoupper(mb_substr($nama, 0, 2));

$stmtNotif = $pdo->prepare("SELECT id, negara_tujuan, created_at FROM pengajuan_pdln WHERE user_id = :user_id and status = 'disetujui' ORDER BY created_at DESC LIMIT 5");
$stmtNotif->execute([':user_id' => $_SESSION['user_id']]);
$notifikasiPdln = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);

$tahunIni  = (int)date('Y');
$tahunLalu = $tahunIni - 1;

// Top 6 negara tahun ini + jumlah tahun lalu, dalam satu query
$stmtTop = $pdo->prepare(
    "SELECT negara_tujuan,
            SUM(tahun_berangkat = :ini)  AS jumlah_ini,
            SUM(tahun_berangkat = :lalu) AS jumlah_lalu
     FROM users
     WHERE negara_tujuan IS NOT NULL
       AND status IN ('disetujui','selesai')
       AND tahun_berangkat IN (:ini2, :lalu2)
     GROUP BY negara_tujuan
     HAVING jumlah_ini > 0
     ORDER BY jumlah_ini DESC, negara_tujuan ASC
     LIMIT 6"
);
$stmtTop->execute([
    ':ini' => $tahunIni,  ':ini2' => $tahunIni,
    ':lalu' => $tahunLalu, ':lalu2' => $tahunLalu,
]);

$klasemen = [];
foreach ($stmtTop->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $ini  = (int)$row['jumlah_ini'];
    $lalu = (int)$row['jumlah_lalu'];

    if ($lalu > 0) {
        $persen = round(($ini - $lalu) / $lalu * 100);
        $naik   = $persen >= 0;
        $label  = abs($persen) . '%';
    } else {
        $naik  = true;
        $label = 'Baru'; // tahun lalu 0 -> persen tidak terdefinisi
    }

    $klasemen[] = [
        'negara' => $row['negara_tujuan'],
        'jumlah' => $ini,
        'label'  => $label,
        'warna'  => $naik ? 'var(--color-success)' : 'var(--color-danger)',
        'panah'  => $naik ? 'M12 19V5m0 0l-6 6m6-6l6 6' : 'M12 5v14m0 0l-6-6m6 6l6-6',
    ];
}

$warnaPeringkat = ['primary', 'info', 'warning', 'danger', 'success', 'primary'];

// Pengajuan berstatus selesai + jumlah anggota + nama tim (tabel Recent Country Visit)
$stmtSelesai = $pdo->prepare(
    "SELECT p.id, p.negara_tujuan, p.tanggal_mulai, p.tanggal_selesai, p.nama_tim,
            COUNT(a.user_id) AS jumlah_anggota
     FROM pengajuan_pdln p
     LEFT JOIN pengajuan_anggota a ON a.pengajuan_id = p.id
     WHERE p.status = :status
     GROUP BY p.id, p.negara_tujuan, p.tanggal_mulai, p.tanggal_selesai, p.nama_tim
     ORDER BY p.tanggal_mulai DESC
     LIMIT 5"
);
$stmtSelesai->execute([':status' => 'selesai']);
$pengajuanSelesai = $stmtSelesai->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en" data-theme="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="icon"
      type="image/svg+xml"
      href='data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><style>.t{fill:%230a0a0a}.m{stroke:%23fafafa}@media (prefers-color-scheme: dark){.t{fill:%23fafafa}.m{stroke:%230a0a0a}}</style><rect class="t" width="512" height="512" rx="112"/><path class="m" d="M 392 144 H 200 A 56 56 0 0 0 200 256 H 312 A 56 56 0 0 1 312 368 H 120" fill="none" stroke-width="76" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    />
    <script>
      // Apply the saved theme before first paint to avoid a flash.
      (function () {
        var t = localStorage.getItem("stisla-theme");
        if (t === "dark" || t === "light") document.documentElement.dataset.theme = t;
      })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
    />
    <title>Dashboard · Meridian</title>

    <link rel="stylesheet" href="assets/css/style.css" />
  </head>
  <body>

      <main class="app-shell__main">
        <header class="navbar" style="display:flex; align-items:center; justify-content:space-between; width:100%;">

          <!-- Spacer kiri (biar menu benar-benar di tengah) -->
          <div style="flex:1;"></div>

          <!-- Menu tengah -->
          <nav class="flex gap-1" style="flex:0 0 auto;">
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Input Pengajuan</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Inbound</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Outbound</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Cek Progress</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Upload Laporan</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Template</a>
            <a href="#" class="button button--ghost button--neutral whitespace-nowrap">Panduan</a>
          </nav>

          <!-- Kanan: notif, dark mode, profil -->
          <div class="flex gap-1 items-center" style="flex:1; justify-content:flex-end;">

            <!-- Notifikasi -->
            <div class="relative">
              <button
                type="button"
                class="button button--ghost button--neutral button--icon-only"
                data-stisla-popover-trigger="topbarNotif"
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-controls="topbarNotif"
                aria-label="Notifications"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="1em"
                  height="1em"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <g fill="none" stroke="currentColor" stroke-width="1.5">
                    <path
                      d="M18.75 9.71v-.705C18.75 5.136 15.726 2 12 2S5.25 5.136 5.25 9.005v.705a4.4 4.4 0 0 1-.692 2.375L3.45 13.81c-1.011 1.575-.239 3.716 1.52 4.214a25.8 25.8 0 0 0 14.06 0c1.759-.498 2.531-2.639 1.52-4.213l-1.108-1.725a4.4 4.4 0 0 1-.693-2.375Z"
                    />
                    <path
                      stroke-linecap="round"
                      d="M7.5 19c.655 1.748 2.422 3 4.5 3s3.845-1.252 4.5-3"
                    />
                  </g>
                </svg>
              </button>
              <div
                class="popover popover--menu w-80 max-w-full shadow-xl"
                id="topbarNotif"
                data-stisla-popover
                data-stisla-popover-placement="bottom-end"
              >
                <div class="popover__header">
                  <h3 class="popover__title">Notifications</h3>
                  <div class="popover__action">
                    <a class="button button--sm button--ghost button--primary button--flush-end"
                      >Mark all as read</a
                    >
                  </div>
                </div>
                <div class="popover__body">
                  <a href="#" class="media items-start">
                    <div class="media__figure mt-1">
                      <span class="icon-box icon-box--primary"
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1em"
                          height="1em"
                          viewBox="0 0 24 24"
                          aria-hidden="true"
                        >
                          <g fill="none" stroke="currentColor" stroke-width="1.5">
                            <path
                              stroke-linecap="round"
                              d="m2 3l.265.088c1.32.44 1.98.66 2.357 1.184S5 5.492 5 6.883V9.5c0 2.828 0 4.243.879 5.121c.878.879 2.293.879 5.121.879h8"
                            />
                            <path
                              d="M7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3Zm9 0a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3ZM5 6h11.45c2.055 0 3.083 0 3.528.674c.444.675.04 1.619-.77 3.508l-.429 1c-.378.882-.567 1.322-.942 1.57c-.376.248-.856.248-1.815.248H5"
                            />
                          </g></svg
                      ></span>
                    </div>
                    <div class="media__content">
                      <div class="media__title">New order #10428</div>
                      <div class="media__description">Acme Corp placed an order for 12 items.</div>
                      <div class="media__meta">5 minutes ago</div>
                    </div>
                  </a>
                  <a href="#" class="media items-start">
                    <div class="media__figure mt-1">
                      <span class="icon-box icon-box--danger"
                        ><svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="1em"
                          height="1em"
                          viewBox="0 0 24 24"
                          aria-hidden="true"
                        >
                          <g fill="none">
                            <path
                              stroke="currentColor"
                              stroke-width="1.5"
                              d="M5.312 10.762C8.23 5.587 9.689 3 12 3s3.77 2.587 6.688 7.762l.364.644c2.425 4.3 3.638 6.45 2.542 8.022S17.786 21 12.364 21h-.728c-5.422 0-8.134 0-9.23-1.572s.117-3.722 2.542-8.022z"
                            />
                            <path
                              stroke="currentColor"
                              stroke-linecap="round"
                              stroke-width="1.5"
                              d="M12 8v5"
                            />
                            <circle cx="12" cy="16" r="1" fill="currentColor" />
                          </g></svg
                      ></span>
                    </div>
                    <div class="media__content">
                      <div class="media__title">Low stock alert</div>
                      <div class="media__description">Headphone Blitz is down to 4 units.</div>
                      <div class="media__meta">1 hour ago</div>
                    </div>
                  </a>
                  <a href="#" class="media items-start">
                    <div class="media__figure mt-1">
                      <span class="avatar avatar--sm avatar--circle" data-stisla-avatar>
                        <img
                          class="avatar__image"
                          src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=facearea&facepad=2.5&w=80&h=80&q=80"
                          alt=""
                        />
                        <span class="avatar__fallback">PP</span>
                      </span>
                    </div>
                    <div class="media__content">
                      <div class="media__title">Priya Patel</div>
                      <div class="media__description">Started following your store.</div>
                      <div class="media__meta">3 hours ago</div>
                    </div>
                  </a>
                </div>
                <div class="popover__footer">
                  <a href="#" class="button button--block button--outline button--neutral"
                    >View all notifications
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="1em"
                      height="1em"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <path
                        fill="currentColor"
                        fill-rule="evenodd"
                        d="M12 2.75a9.25 9.25 0 1 0 0 18.5a9.25 9.25 0 0 0 0-18.5M1.25 12C1.25 6.063 6.063 1.25 12 1.25S22.75 6.063 22.75 12S17.937 22.75 12 22.75S1.25 17.937 1.25 12m11.22-3.53a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1 0 1.06l-3 3a.75.75 0 1 1-1.06-1.06l1.72-1.72H8a.75.75 0 0 1 0-1.5h6.19l-1.72-1.72a.75.75 0 0 1 0-1.06"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </a>
                </div>
              </div>
            </div>

            <!-- Dark mode -->
            <button
              type="button"
              class="button button--ghost button--neutral button--icon-only"
              data-theme-toggle
              aria-label="Toggle theme"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="1em"
                height="1em"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path
                  fill="currentColor"
                  d="m21.067 11.857l-.642-.388zm-8.924-8.924l-.388-.642zM21.25 12A9.25 9.25 0 0 1 12 21.25v1.5c5.937 0 10.75-4.813 10.75-10.75zM12 21.25A9.25 9.25 0 0 1 2.75 12h-1.5c0 5.937 4.813 10.75 10.75 10.75zM2.75 12A9.25 9.25 0 0 1 12 2.75v-1.5C6.063 1.25 1.25 6.063 1.25 12zm12.75 2.25A5.75 5.75 0 0 1 9.75 8.5h-1.5a7.25 7.25 0 0 0 7.25 7.25zm4.925-2.781A5.75 5.75 0 0 1 15.5 14.25v1.5a7.25 7.25 0 0 0 6.21-3.505zM9.75 8.5a5.75 5.75 0 0 1 2.781-4.925l-.776-1.284A7.25 7.25 0 0 0 8.25 8.5zM12 2.75a.38.38 0 0 1-.268-.118a.3.3 0 0 1-.082-.155c-.004-.031-.002-.121.105-.186l.776 1.284c.503-.304.665-.861.606-1.299c-.062-.455-.42-1.026-1.137-1.026zm9.71 9.495c-.066.107-.156.109-.187.105a.3.3 0 0 1-.155-.082a.38.38 0 0 1-.118-.268h1.5c0-.717-.571-1.075-1.026-1.137c-.438-.059-.995.103-1.299.606z"
                />
              </svg>
            </button>

            <!-- Profil -->
            <div class="menu">
              <button
                type="button"
                class="button button--ghost button--neutral flex items-center gap-2"
                data-stisla-menu-trigger="topbarUser"
                aria-haspopup="menu"
                aria-expanded="false"
                aria-controls="topbarUser"
              >
                <span class="hidden sm:inline font-medium"><?= htmlspecialchars($nama) ?></span>
                <span class="avatar avatar--sm avatar--circle" data-stisla-avatar>
                  <img
                    class="avatar__image"
                    src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=facearea&facepad=2.5&w=80&h=80&q=80"
                    alt=""
                  />
                  <span class="avatar__fallback"><?= htmlspecialchars($inisial) ?></span>
                </span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="1em"
                  height="1em"
                  viewBox="0 0 24 24"
                  aria-hidden="true"
                >
                  <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="m19 9l-7 6l-7-6"
                  />
                </svg>
              </button>
              <div
                class="menu__popup w-48"
                id="topbarUser"
                data-stisla-menu
                role="menu"
                data-state="closed"
              >
                <div class="menu__group" role="group" aria-labelledby="topbarUserHead">
                  <h3 class="menu__group-label" id="topbarUserHead"><?= htmlspecialchars($nama) ?> · <?= htmlspecialchars($role) ?></h3>
                  <a href="/meridian/settings.html" class="menu__item" role="menuitem"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="1em"
                      height="1em"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="6" r="4" />
                        <path
                          d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5Z"
                        />
                      </g></svg
                    >Profile</a
                  >
                  <a href="/meridian/settings.html" class="menu__item" role="menuitem"
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="1em"
                      height="1em"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="3" />
                        <path
                          d="M13.765 2.152C13.398 2 12.932 2 12 2s-1.398 0-1.765.152a2 2 0 0 0-1.083 1.083c-.092.223-.129.484-.143.863a1.62 1.62 0 0 1-.79 1.353a1.62 1.62 0 0 1-1.567.008c-.336-.178-.579-.276-.82-.308a2 2 0 0 0-1.478.396C4.04 5.79 3.806 6.193 3.34 7s-.7 1.21-.751 1.605a2 2 0 0 0 .396 1.479c.148.192.355.353.676.555c.473.297.777.803.777 1.361s-.304 1.064-.777 1.36c-.321.203-.529.364-.676.556a2 2 0 0 0-.396 1.479c.052.394.285.798.75 1.605c.467.807.7 1.21 1.015 1.453a2 2 0 0 0 1.479.396c.24-.032.483-.13.819-.308a1.62 1.62 0 0 1 1.567.008c.483.28.77.795.79 1.353c.014.38.05.64.143.863a2 2 0 0 0 1.083 1.083C10.602 22 11.068 22 12 22s1.398 0 1.765-.152a2 2 0 0 0 1.083-1.083c.092-.223.129-.483.143-.863c.02-.558.307-1.074.79-1.353a1.62 1.62 0 0 1 1.567-.008c.336.178.579.276.819.308a2 2 0 0 0 1.479-.396c.315-.242.548-.646 1.014-1.453s.7-1.21.751-1.605a2 2 0 0 0-.396-1.479c-.148-.192-.355-.353-.676-.555A1.62 1.62 0 0 1 19.562 12c0-.558.304-1.064.777-1.36c.321-.203.529-.364.676-.556a2 2 0 0 0 .396-1.479c-.052-.394-.285-.798-.75-1.605c-.467-.807-.7-1.21-1.015-1.453a2 2 0 0 0-1.479-.396c-.24.032-.483.13-.82.308a1.62 1.62 0 0 1-1.566-.008a1.62 1.62 0 0 1-.79-1.353c-.014-.38-.05-.64-.143-.863a2 2 0 0 0-1.083-1.083Z"
                        />
                      </g></svg
                    >Settings</a
                  >
                </div>
                <hr class="menu__separator" role="separator" />
                <a href="/meridian/login.html" class="menu__item" role="menuitem"
                  ><svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="1em"
                    height="1em"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                  >
                    <g
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-width="1.5"
                    >
                      <path
                        d="M9.002 7c.012-2.175.109-3.353.877-4.121C10.758 2 12.172 2 15 2h1c2.829 0 4.243 0 5.122.879C22 3.757 22 5.172 22 8v8c0 2.828 0 4.243-.878 5.121C20.242 22 18.829 22 16 22h-1c-2.828 0-4.242 0-5.121-.879c-.768-.768-.865-1.946-.877-4.121"
                      />
                      <path stroke-linejoin="round" d="M15 12H2m0 0l3.5-3M2 12l3.5 3" />
                    </g></svg
                  >Log out</a
                >
              </div>
            </div>

          </div>
        </header>

        <div class="page content" style="margin-left: -24px;">
          <div class="content__container" style="margin-left: 20px;">
            <header class="page__header">
              <h1 class="page__title">Welcome back, <span><?= htmlspecialchars($nama) ?></span> 👋</h1>
            </header>
            <div class="page__body">
              <section class="page__section">
                <div class="grid grid-cols-12 gap-4">
                  <div class="col-span-12 xl:col-span-4">
                    <div class="card card--stat">
                      <div class="card__body">
                        <div class="card__title">Order Statistics</div>

                        <div class="stat">
                          <div class="stat__value">59</div>
                          <div class="stat__meta">
                            <span class="stat__label text-eyebrow">Total Orders · This Month</span>
                            <span class="badge badge--soft badge--success"
                              ><svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="1em"
                                height="1em"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                              >
                                <path
                                  fill="none"
                                  stroke="currentColor"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.5"
                                  d="m19 15l-7-6l-7 6"
                                />
                              </svg>
                              11%</span
                            >
                          </div>
                        </div>

                        <div class="meter meter--block meter--lg meter--stat">
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--warning" style="width: 41%"></span>
                            <span class="meter__bar meter__bar--primary" style="width: 20%"></span>
                            <span class="meter__bar meter__bar--success" style="width: 39%"></span>
                          </div>
                        </div>

                        <div class="stat-detail">
                          <div class="stat-detail__col">
                            <span class="stat-detail__label text-eyebrow">
                              <span class="indicator indicator--warning"></span>
                              Pending
                            </span>
                            <span class="stat-detail__value">24</span>
                            <span class="stat-detail__pct">41%</span>
                          </div>

                          <div class="stat-detail__col">
                            <span class="stat-detail__label text-eyebrow">
                              <span class="indicator indicator--primary"></span>
                              Shipping
                            </span>
                            <span class="stat-detail__value">12</span>
                            <span class="stat-detail__pct">20%</span>
                          </div>

                          <div class="stat-detail__col">
                            <span class="stat-detail__label text-eyebrow">
                              <span class="indicator indicator--success"></span>
                              Completed
                            </span>
                            <span class="stat-detail__value">23</span>
                            <span class="stat-detail__pct">39%</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                    <div class="card card--stat">
                      <div class="card__body">
                        <div class="flex justify-between items-center">
                          <span class="icon-box icon-box--primary icon-box--lg"
                            ><svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="1em"
                              height="1em"
                              viewBox="0 0 24 24"
                              aria-hidden="true"
                            >
                              <g fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <path
                                  stroke-linecap="round"
                                  d="M12 17v1m0-12v1m3 2.5C15 8.12 13.657 7 12 7S9 8.12 9 9.5s1.343 2.5 3 2.5s3 1.12 3 2.5s-1.343 2.5-3 2.5s-3-1.12-3-2.5"
                                />
                              </g></svg
                          ></span>
                          <span class="badge badge--soft badge--success"
                            ><svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="1em"
                              height="1em"
                              viewBox="0 0 24 24"
                              aria-hidden="true"
                            >
                              <path
                                fill="none"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="m19 15l-7-6l-7 6"
                              />
                            </svg>
                            8.2%</span
                          >
                        </div>
                        <div class="stat">
                          <div class="stat__value"><span class="stat__unit">$</span>187,13</div>
                          <div class="stat__label text-eyebrow">Available Balance</div>
                        </div>
                        <div id="sparkBalance" class="sparkline sparkline--hero"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                    <div class="card card--stat">
                      <div class="card__body">
                        <div class="flex justify-between items-center">
                          <span class="icon-box icon-box--success icon-box--lg"
                            ><svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="1em"
                              height="1em"
                              viewBox="0 0 24 24"
                              aria-hidden="true"
                            >
                              <g fill="none">
                                <path
                                  stroke="currentColor"
                                  stroke-width="1.5"
                                  d="M3.794 12.03C4.331 9.342 4.6 8 5.487 7.134a4 4 0 0 1 .53-.434C7.04 6 8.41 6 11.15 6h1.703c2.739 0 4.108 0 5.13.7q.285.196.53.435C19.4 8 19.67 9.343 20.207 12.03c.771 3.856 1.157 5.784.269 7.15q-.241.373-.56.683C18.75 21 16.785 21 12.853 21H11.15c-3.933 0-5.899 0-7.065-1.138a4 4 0 0 1-.559-.683c-.888-1.366-.502-3.294.27-7.15Z"
                                />
                                <circle cx="15" cy="9" r="1" fill="currentColor" />
                                <circle cx="9" cy="9" r="1" fill="currentColor" />
                                <path
                                  stroke="currentColor"
                                  stroke-linecap="round"
                                  stroke-width="1.5"
                                  d="M9 6V5a3 3 0 1 1 6 0v1"
                                />
                              </g></svg
                          ></span>
                          <span class="badge badge--soft badge--success"
                            ><svg
                              xmlns="http://www.w3.org/2000/svg"
                              width="1em"
                              height="1em"
                              viewBox="0 0 24 24"
                              aria-hidden="true"
                            >
                              <path
                                fill="none"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="m19 15l-7-6l-7 6"
                              />
                            </svg>
                            23%</span
                          >
                        </div>
                        <div class="stat">
                          <div class="stat__value">4,732</div>
                          <div class="stat__label text-eyebrow">Units Sold · MTD</div>
                        </div>
                        <div id="sparkSales" class="sparkline sparkline--hero"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <section class="page__section">
                <div class="grid grid-cols-12 gap-4 items-start">
                  <div class="col-span-12 xl:col-span-8">
                    <div class="card">
                      <div class="card__header">
                        <span class="card__title">Sales Performance</span>
                        <div class="chart-legend">
                          <span class="chart-legend__item">
                            <span class="indicator indicator--primary"></span>
                            Sales
                          </span>
                          <span class="chart-legend__item">
                            <span class="indicator indicator--danger"></span>
                            Budget
                          </span>
                        </div>
                      </div>
                      <div class="card__body">
                        <div class="chart-scroll">
                          <div id="salesChart" class="chart"></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 xl:col-span-4">
                    <div class="card">
                      <div class="card__header">
                        <span class="card__title">Top Negara yang Dikunjungi</span>
                      </div>
                      <div class="card__body">
                        <span class="card__main" style="font-style: italic; font-size: 9px; color: grey; margin-top: -11px;">*Persentase berdasarkan jumlah pengajuan 2026 dibandingkan 2025</span>
                      </div>

                      <div class="media-list">
                        <?php if (empty($klasemen)): ?>
                          <div class="media media--seamless">
                            <div class="media__content">
                              <div class="media__meta">Belum ada data tahun <?= $tahunIni ?></div>
                            </div>
                          </div>
                        <?php endif; ?>

                        <?php foreach ($klasemen as $i => $k): $w = $warnaPeringkat[$i]; ?>
                          <div class="media media--seamless">
                            <span
                              class="avatar"
                              style="--avatar-bg: color-mix(in oklch, var(--color-<?= $w ?>) 16%, transparent); --avatar-color: var(--color-<?= $w ?>); font-weight: 600;"
                            >
                              <?= $i + 1 ?>
                            </span>
                            <div class="media__content">
                              <div class="media__title"><?= htmlspecialchars($k['negara']) ?></div>
                            </div>
                            <div class="text-end">
                              <div class="font-semibold"><?= $k['jumlah'] ?> anggota</div>
                              <div class="text-xs" style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; color: <?= $k['warna'] ?>;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                                  <path
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="<?= $k['panah'] ?>"
                                  />
                                </svg>
                                <span><?= $k['label'] ?></span>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <div class="card__footer">
                        <a href="#" class="button button--lg button--neutral button--block"
                          >View All</a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <section class="page__section">
                <div class="grid grid-cols-12 gap-4">
                  <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="card h-full">
                      <div class="card__header">
                        <span class="card__title">Conversion Funnel</span>
                      </div>
                      <div class="card__body gap-5">
                        <div class="meter">
                          <span class="meter__label">Sessions</span>
                          <span class="meter__value text-foreground font-medium"> 18,420 </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 100%"></span>
                          </div>
                        </div>

                        <div class="meter">
                          <span class="meter__label">Product Views</span>
                          <span class="meter__value text-foreground font-medium">
                            12,100
                            <span class="text-xs text-muted-foreground">-34%</span>
                          </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 66%"></span>
                          </div>
                        </div>

                        <div class="meter">
                          <span class="meter__label">Add to Cart</span>
                          <span class="meter__value text-foreground font-medium">
                            7,250
                            <span class="text-xs text-muted-foreground">-40%</span>
                          </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 39%"></span>
                          </div>
                        </div>

                        <div class="meter">
                          <span class="meter__label">Checkout</span>
                          <span class="meter__value text-foreground font-medium">
                            3,980
                            <span class="text-xs text-muted-foreground">-45%</span>
                          </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 22%"></span>
                          </div>
                        </div>

                        <div class="meter">
                          <span class="meter__label">Payment Info</span>
                          <span class="meter__value text-foreground font-medium">
                            2,890
                            <span class="text-xs text-muted-foreground">-27%</span>
                          </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 16%"></span>
                          </div>
                        </div>

                        <div class="meter">
                          <span class="meter__label">Purchased</span>
                          <span class="meter__value text-foreground font-medium">
                            2,140
                            <span class="text-xs text-muted-foreground">-26%</span>
                          </span>
                          <div class="meter__track">
                            <span class="meter__bar meter__bar--primary" style="width: 12%"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="card h-full">
                      <div class="card__header">
                        <span class="card__title">Fulfillment</span>
                      </div>
                      <div class="card__body">
                        <div id="fulfillmentChart" class="chart"></div>
                        <div class="stat-detail">
                          <div class="stat-detail__col items-center">
                            <div class="stat-detail__label text-eyebrow">Avg ship</div>
                            <div class="stat-detail__value">1.2d</div>
                          </div>
                          <div class="stat-detail__col items-center">
                            <div class="stat-detail__label text-eyebrow">Returns</div>
                            <div class="stat-detail__value">0.4%</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="card h-full">
                      <div class="card__header">
                        <span class="card__title">Order Heatmap</span>
                      </div>
                      <div class="card__body">
                        <div class="flex items-center">
                          <span class="text-lg font-light">Fri · 6PM</span>
                          <span class="text-xs text-muted-foreground ms-2">Peak window</span>
                        </div>
                        <div id="heatmapChart" class="chart"></div>
                        <div class="heatmap-legend">
                          <span class="heatmap-legend__caption">Less</span>
                          <span class="heatmap-legend__swatch opacity-15"></span>
                          <span class="heatmap-legend__swatch opacity-40"></span>
                          <span class="heatmap-legend__swatch opacity-65"></span>
                          <span class="heatmap-legend__swatch opacity-85"></span>
                          <span class="heatmap-legend__swatch opacity-100"></span>
                          <span class="heatmap-legend__caption">More</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 sm:col-span-6 xl:col-span-3">
                    <div class="card h-full">
                      <div class="card__header">
                        <span class="card__title">Recent Activity</span>
                      </div>
                      <div class="card__body">
                        <ol class="timeline">
                          <li class="timeline__item">
                            <span class="timeline__marker timeline__marker--success"></span>
                            <div class="timeline__body">
                              <div class="timeline__title">
                                New order <a href="#">#QC-7841</a> from Acme Corp
                              </div>
                              <div class="timeline__time">2 min ago</div>
                            </div>
                          </li>

                          <li class="timeline__item">
                            <span class="timeline__marker timeline__marker--primary"></span>
                            <div class="timeline__body">
                              <div class="timeline__title">
                                Payment captured · <a href="#">$2,410</a>
                              </div>
                              <div class="timeline__time">9 min ago</div>
                            </div>
                          </li>

                          <li class="timeline__item">
                            <span class="timeline__marker timeline__marker--warning"></span>
                            <div class="timeline__body">
                              <div class="timeline__title">Low stock alert · Ion Membrane Pack</div>
                              <div class="timeline__time">22 min ago</div>
                            </div>
                          </li>

                          <li class="timeline__item">
                            <span class="timeline__marker timeline__marker--danger"></span>
                            <div class="timeline__body">
                              <div class="timeline__title">
                                Refund issued <a href="#">#QC-7802</a>
                              </div>
                              <div class="timeline__time">41 min ago</div>
                            </div>
                          </li>

                          <li class="timeline__item">
                            <span class="timeline__marker timeline__marker--primary"></span>
                            <div class="timeline__body">
                              <div class="timeline__title">New customer · Globex onboarded</div>
                              <div class="timeline__time">1 hr ago</div>
                            </div>
                          </li>
                        </ol>
                      </div>
                    </div>
                  </div>
                </div>
              </section>

              <section class="page__section">
                <div class="card">
                  <div class="card__header">
                    <span class="card__title">Recent Country Visit</span>
                    <a
                      href="/meridian/orders.html"
                      class="ms-auto button button--neutral button--ghost button--sm"
                      >View all</a
                    >
                  </div>

                  <div class="table-responsive">
                    <table class="table table--hover table--align-middle">
                      <thead class="table__head--alt">
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Negara</th>
                          <th scope="col">Date</th>
                          <th scope="col">Anggota</th>
                          <th scope="col">Tim</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($pengajuanSelesai)): ?>
                        <tr>
                          <td colspan="5" class="text-muted-foreground">Belum ada pengajuan yang selesai.</td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($pengajuanSelesai as $p): ?>
                        <tr>
                          <th scope="row">
                            <a href="/meridian/order-detail.html?id=<?= (int)$p['id'] ?>" class="link"
                              ><code>#<?= (int)$p['id'] ?></code></a
                            >
                          </th>
                          <td>
                            <div class="font-medium"><?= htmlspecialchars($p['negara_tujuan']) ?></div>
                          </td>
                          <td><?= date('d M', strtotime($p['tanggal_mulai'])) ?> – <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
                          <td><?= (int)$p['jumlah_anggota'] ?> orang</td>
                          <td>
                            <span class="badge badge--soft badge--info"><?= htmlspecialchars($p['nama_tim'] ?? '-') ?></span>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </section>
            </div>
          </div>
        </div>
      </main>

    <!-- Third-party (CDN in both dev and prod). Loads before main.js so charts.js sees window.ApexCharts. -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3"></script>

    <script
      type="module"
      src="https://cdn.jsdelivr.net/npm/@stisla/vanilla@3/dist/stisla.js"
    ></script>
    <script src="assets/js/app-shell.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/charts.js"></script>
    <script src="assets/js/order-form.js"></script>
    <script src="assets/js/table-select.js"></script>
  </body>
</html>