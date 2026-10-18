<?php
// Role ditentukan dari domain email. null = domain tidak diizinkan
function tentukanRole($email) {
    $email = strtolower($email);
    if (preg_match('/@admin\.com$/', $email)) return 'admin';
    if (preg_match('/@stu\.pnj\.ac\.id$/', $email)) return 'mahasiswa';
    if (preg_match('/@[a-z0-9-]+\.pnj\.ac\.id$/', $email)) return 'dosen';
    return null;
}

// Pecah bagian sebelum @ jadi kata-kata nama; segmen berangka (tik25) dibuang
function pecahNama($email) {
    $local  = strtolower(explode('@', $email)[0]);
    $bagian = array_filter(explode('.', $local), fn($p) => $p !== '' && !preg_match('/\d/', $p));
    return array_values($bagian) ?: ['user'];
}

// Nama panggilan: kata ke-2, kalau cuma 1 kata pakai kata itu
function ambilNama($email) {
    $b = pecahNama($email);
    return ucfirst($b[1] ?? $b[0]);
}

// Nama lengkap untuk kolom `nama`: "Mohamad Malik Fadillah Arwin"
function ambilNamaLengkap($email) {
    return ucwords(implode(' ', pecahNama($email)));
}