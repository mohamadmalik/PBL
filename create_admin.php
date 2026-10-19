<?php
$pdo = new PDO("mysql:host=localhost;dbname=pbl semua;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ---------- Skema tambahan (aman dijalankan berulang) ----------
$pdo->exec("ALTER TABLE pengajuan_pdln ADD COLUMN IF NOT EXISTS nama_tim VARCHAR(100) NULL AFTER tujuan_kegiatan");
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS pengajuan_anggota (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        pengajuan_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        UNIQUE KEY uniq_anggota (pengajuan_id, user_id),
        FOREIGN KEY (pengajuan_id) REFERENCES pengajuan_pdln(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Bersihkan data pengajuan lama supaya seeding bisa diulang
$pdo->exec("DELETE FROM pengajuan_anggota");
$pdo->exec("DELETE FROM pengajuan_pdln");

// Singkatan: 'rizqa.cahya' -> 'rizqa.cahya.tik25@stu.pnj.ac.id'
function mhs($nama) { return "$nama.tik25@stu.pnj.ac.id"; }

// [negara, tujuan kegiatan, tanggal mulai, tanggal selesai, status, nama tim, anggota]
// Anggota pertama = ketua (disimpan di kolom user_id pengajuan_pdln)
$pengajuan = [
    ['Belanda',       'Workshop UI/UX Internasional',      '2026-09-05', '2026-09-10', 'selesai',            'Tim Desain Amsterdam', [mhs('azzahra.siti'), mhs('indra.laksana')]],
    ['Korea Selatan', 'Lomba Robotik Internasional',       '2026-08-15', '2026-08-20', 'selesai',            'Tim Robotik Seoul',    [mhs('alvin.pratama'), mhs('farhan.kurnia'), mhs('lutfi.hakim')]],
    ['Australia',     'Studi Banding Software Engineering','2026-07-08', '2026-07-14', 'selesai',            'Tim Aussie Dev',       [mhs('andika.wijaya'), mhs('raditya.pradipta')]],
    ['Jerman',        'Konferensi Machine Learning',       '2026-06-20', '2026-06-27', 'selesai',            'Tim AI Berlin',        [mhs('daniel.sitorus'), mhs('amanda.sekar'), mhs('naufal.hidayat')]],
    ['Jepang',        'Pertukaran Pelajar Teknologi',      '2026-05-02', '2026-05-12', 'selesai',            'Tim Sakura',           [mhs('muhammad.rizky'), mhs('dion.yudha'), mhs('tariq.ziyad')]],
    ['Singapura',     'Kompetisi Cybersecurity',           '2026-03-10', '2026-03-15', 'selesai',            'Tim Cyber PNJ',        [mhs('rizqa.cahya'), mhs('naveta.chandra.choirullah'), mhs('ahmad.fauzi'), mhs('hanifah.salsa')]],
    ['Malaysia',      'Hackathon Regional',                '2025-11-04', '2025-11-07', 'selesai',            'Tim Hack KL',          [mhs('kevin.sanjaya'), mhs('citra.lestari'), mhs('aisyah.nurul')]],
    ['Inggris',       'Seminar Data Science',              '2026-12-12', '2026-12-18', 'pending',            'Tim Data London',      [mhs('annisa.rahma'), mhs('putri.syifa')]],
    ['Thailand',      'Lomba Internet of Things',          '2027-01-12', '2027-01-16', 'verifikasi_dokumen', 'Tim IoT Bangkok',      [mhs('bayu.pamungkas'), mhs('rafi.akbar')]],
];

$cariUser = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$insPengajuan = $pdo->prepare(
    "INSERT INTO pengajuan_pdln (user_id, negara_tujuan, tujuan_kegiatan, nama_tim, tanggal_mulai, tanggal_selesai, status)
     VALUES (:user_id, :negara, :tujuan, :tim, :mulai, :selesai, :status)"
);
$insAnggota = $pdo->prepare("INSERT IGNORE INTO pengajuan_anggota (pengajuan_id, user_id) VALUES (:pid, :uid)");

foreach ($pengajuan as [$negara, $tujuan, $mulai, $selesai, $status, $tim, $anggota]) {
    // Ambil id user tiap anggota (user harus sudah di-seed lewat create_akun.php)
    $ids = [];
    foreach ($anggota as $email) {
        $cariUser->execute([':email' => $email]);
        $id = $cariUser->fetchColumn();
        if ($id === false) {
            echo "  SKIP anggota (user belum ada): $email\n";
            continue;
        }
        $ids[] = (int)$id;
    }
    if (empty($ids)) {
        echo "SKIP pengajuan $negara: tidak ada anggota valid\n";
        continue;
    }

    try {
        $insPengajuan->execute([
            ':user_id' => $ids[0],
            ':negara'  => $negara,
            ':tujuan'  => $tujuan,
            ':tim'     => $tim,
            ':mulai'   => $mulai,
            ':selesai' => $selesai,
            ':status'  => $status,
        ]);
        $pid = (int)$pdo->lastInsertId();
        foreach ($ids as $uid) {
            $insAnggota->execute([':pid' => $pid, ':uid' => $uid]);
        }
        echo "OK: #$pid $negara ($status, $tim, " . count($ids) . " anggota)\n";
    } catch (PDOException $e) {
        echo "GAGAL: $negara -> " . $e->getMessage() . "\n";
    }
}