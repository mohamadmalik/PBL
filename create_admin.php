<?php
require_once 'helpers.php';

$pdo = new PDO("mysql:host=localhost;dbname=pbl semua;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$password = 'admin123'; // ganti sesuai kebutuhan
$statusDefault = 'selesai';

// email => [negara_tujuan, status]  (status null = pakai default)
$emails = [
    'admin@admin.com'                                   => [null, null, null],
    'mohamad.malik.fadillah.arwin.tik25@stu.pnj.ac.id'  => ['Jepang', 'ditolak', 2026],
    'kesya.rizqia.anak.pnj.tik25@stu.pnj.ac.id'         => ['Singapura', 'pending', 2025],
    'rizqa.cahya.tik25@stu.pnj.ac.id'                   => ['Singapura', 'disetujui', 2026],
    'naveta.chandra.choirullah.tik25@stu.pnj.ac.id'     => ['Singapura', null, 2026],
    'adelia.putri.tik25@stu.pnj.ac.id'      => ['Jepang', 'disetujui', 2025],
    'ahmad.fauzi.tik25@stu.pnj.ac.id'       => ['Singapura', null, 2026],
    'aisyah.nurul.tik25@stu.pnj.ac.id'      => ['Malaysia', 'ditolak', 2025],
    'alvin.pratama.tik25@stu.pnj.ac.id'     => ['Korea Selatan', null, 2026],
    'amanda.sekar.tik25@stu.pnj.ac.id'      => ['Jerman', null, 2025],
    'andika.wijaya.tik25@stu.pnj.ac.id'     => ['Australia', null, 2026],
    'annisa.rahma.tik25@stu.pnj.ac.id'      => ['Inggris', null, 2026],
    'arya.kusuma.tik25@stu.pnj.ac.id'       => ['Jepang', 'ditolak', 2025],
    'azzahra.siti.tik25@stu.pnj.ac.id'      => ['Belanda', null, 2026],
    'bagas.satria.tik25@stu.pnj.ac.id'      => ['Singapura', null, 2025],
    'bayu.pamungkas.tik25@stu.pnj.ac.id'   => ['Thailand', null, 2026],
    'citra.lestari.tik25@stu.pnj.ac.id'     => ['Malaysia', null, 2025],
    'daniel.sitorus.tik25@stu.pnj.ac.id'    => ['Jerman', null, 2026],
    'dion.yudha.tik25@stu.pnj.ac.id'        => ['Jepang', null, 2026],
    'farhan.kurnia.tik25@stu.pnj.ac.id'     => ['Korea Selatan', 'ditolak', 2025],
    'fiona.angelica.tik25@stu.pnj.ac.id'   => ['Taiwan', null, 2026],
    'gilang.ramadhan.tik25@stu.pnj.ac.id'  => ['Australia', null, 2025],
    'hanifah.salsa.tik25@stu.pnj.ac.id'     => ['Singapura', null, 2026],
    'ikbal.maulana.tik25@stu.pnj.ac.id'     => ['Jepang', null, 2025],
    'indra.laksana.tik25@stu.pnj.ac.id'     => ['Belanda', 'ditolak', 2026],
    'kevin.sanjaya.tik25@stu.pnj.ac.id'    => ['Malaysia', 'disetujui', 2025],
    'lutfi.hakim.tik25@stu.pnj.ac.id'       => ['Korea Selatan', 'menunggu', 2026],
    'muhammad.rizky.tik25@stu.pnj.ac.id'    => ['Jepang', 'disetujui', 2026],
    'nadia.utami.tik25@stu.pnj.ac.id'       => ['Singapura', 'ditolak', 2025],
    'naufal.hidayat.tik25@stu.pnj.ac.id'    => ['Jerman', 'menunggu', 2026],
    'putri.syifa.tik25@stu.pnj.ac.id'       => ['Inggris', 'disetujui', 2025],
    'raditya.pradipta.tik25@stu.pnj.ac.id'  => ['Australia', null, 2026],
    'rafi.akbar.tik25@stu.pnj.ac.id'        => ['Thailand', 'disetujui', 2025],
    'salsa.bila.tik25@stu.pnj.ac.id'        => ['Vietnam', 'menunggu', 2026],
    'tariq.ziyad.tik25@stu.pnj.ac.id'       => ['Jepang', 'disetujui', 2026],
    // ...sisanya sama polanya
];

$stmt = $pdo->prepare(
    "INSERT INTO users (nama, email, password, role, negara_tujuan, tahun_berangkat, status)
     VALUES (:nama, :email, :password, :role, :negara, :tahun, :status)
     ON DUPLICATE KEY UPDATE nama = VALUES(nama), password = VALUES(password), role = VALUES(role),
                             negara_tujuan = VALUES(negara_tujuan), tahun_berangkat = VALUES(tahun_berangkat),
                             status = VALUES(status)"
);

foreach ($emails as $email => [$negara, $status, $tahun]) {
    $role = tentukanRole($email);
    if ($role === null) {
        echo "SKIP (domain tidak diizinkan): $email\n";
        continue;
    }
    $status = $status ?? $statusDefault;
    $stmt->execute([
        ':nama'     => ambilNamaLengkap($email),
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':role'     => $role,
        ':negara'   => $negara,
        ':tahun'    => $tahun,
        ':status'   => $status,
    ]);
    echo "OK: $email ($role, " . ($negara ?? '-') . ", " . ($tahun ?? '-') . ", $status)\n";
}