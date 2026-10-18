<?php
require_once 'helpers.php';

$pdo = new PDO("mysql:host=localhost;dbname=pbl semua;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$password = 'password123'; // ganti sesuai kebutuhan

$emails = [
    'admin@admin.com',
    'mohamad.malik.fadillah.arwin.tik25@stu.pnj.ac.id',
    'budi.santoso@tik.pnj.ac.id',
];

$stmt = $pdo->prepare(
    "INSERT INTO users (nama, email, password, role)
     VALUES (:nama, :email, :password, :role)
     ON DUPLICATE KEY UPDATE nama = VALUES(nama), password = VALUES(password), role = VALUES(role)"
);

foreach ($emails as $email) {
    $role = tentukanRole($email);
    if ($role === null) {
        echo "SKIP (domain tidak diizinkan): $email\n";
        continue;
    }
    $stmt->execute([
        ':nama'     => ambilNamaLengkap($email),
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':role'     => $role,
    ]);
    echo "OK: $email ($role)\n";
}