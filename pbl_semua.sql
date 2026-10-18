-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Okt 2026 pada 16.58
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pbl semua`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_pdln`
--

CREATE TABLE `dokumen_pdln` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pengajuan_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_dokumen` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status_dokumen` enum('pending','valid','revisi') DEFAULT 'pending',
  `catatan_revisi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_pdln`
--

CREATE TABLE `pengajuan_pdln` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `negara_tujuan` varchar(255) NOT NULL,
  `tujuan_kegiatan` text NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('baru_diajukan','verifikasi_dokumen','pending','disetujui','selesai','revisi','ditolak') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `file_sp_setneg` varchar(255) DEFAULT NULL,
  `file_lpj` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa','dosen') NOT NULL DEFAULT 'mahasiswa',
  `nik` varchar(16) DEFAULT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_paspor` varchar(255) DEFAULT NULL,
  `negara_tujuan` varchar(100) DEFAULT NULL,
  `tahun_berangkat` smallint(6) DEFAULT NULL,
  `status` enum('baru_diajukan','verifikasi_dokumen','pending','disetujui','selesai','revisi','ditolak') NOT NULL DEFAULT 'pending',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `nik`, `file_ktp`, `file_paspor`, `negara_tujuan`, `tahun_berangkat`, `status`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin KUI PNJ', 'admin@pnj.ac.id', '$2y$10$hlQTcdDvIgeF..lNrFhrm.XPXsNRSPpoFCxS29PGIdUzZnf6KF07q', 'admin', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, '2026-10-17 11:39:01', '2026-10-17 11:39:01'),
(4, 'Admin', 'admin@admin.com', '$2y$10$Rwtl6Em4BPsJUVgPWYA8BOmq1p08d2pLmfru9rMYbsY/tYMDhkx0i', 'admin', NULL, NULL, NULL, NULL, NULL, 'selesai', NULL, '2026-10-18 06:35:38', '2026-10-18 14:40:52'),
(5, 'Mohamad Malik Fadillah Arwin', 'mohamad.malik.fadillah.arwin.tik25@stu.pnj.ac.id', '$2y$10$gUh3S17VAfygqOM9hhUKeOFOfHNomjGm0zqqrGKDdXencOXHoz1l.', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2026, 'ditolak', NULL, '2026-10-18 06:35:38', '2026-10-18 14:40:52'),
(6, 'Budi Santoso', 'budi.santoso@tik.pnj.ac.id', '$2y$10$jwB04xr5gA0YvL3lXl96puzQ7kEakQyJlPygfL7MBaNAZrogegLY2', 'dosen', NULL, NULL, NULL, 'Jepang', NULL, 'disetujui', NULL, '2026-10-18 06:35:38', '2026-10-18 13:28:27'),
(10, 'Kesya Rizqia Anak Pnj', 'kesya.rizqia.anak.pnj.tik25@stu.pnj.ac.id', '$2y$10$zZ0iMLh6fJnl2pnNotzNZuW4joAJaVUunnyujNfdbi0Fo1XFIrpSi', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2025, 'pending', NULL, '2026-10-18 11:10:39', '2026-10-18 14:40:53'),
(11, 'Rizqa Cahya', 'rizqa.cahya.tik25@stu.pnj.ac.id', '$2y$10$zRHVGYrHOHz.kw0YHGhfRuci/09.LmYEWjt4r0k8ZRgmExa5MayOe', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2026, 'disetujui', NULL, '2026-10-18 11:10:39', '2026-10-18 14:40:53'),
(12, 'Naveta Chandra Choirullah', 'naveta.chandra.choirullah.tik25@stu.pnj.ac.id', '$2y$10$SMci18WRdKmjRuG2RDej/..qMOJ9jIcarV9P1bxB/FEOGhjXVaMHW', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2026, 'selesai', NULL, '2026-10-18 11:10:39', '2026-10-18 14:40:53'),
(13, 'Dhani Wira Saputra', 'dhani.wira.saputra.tik25@stu.pnj.ac.id', '$2y$10$WqmXE5PdsGSNQeFcsWATCec6atRd66dsOsSaQs7zQ0QQID7NuBGAy', 'mahasiswa', NULL, NULL, NULL, 'Belanda', NULL, 'disetujui', NULL, '2026-10-18 11:10:39', '2026-10-18 13:28:28'),
(14, 'Fajar Septian', 'fajar.septian@tik.pnj.ac.id', '$2y$10$gwA2u8.VUtsavhKce7QH7.Zl3DghJ/w9LiDEn/HWCb7DtwZ/EeVI2', 'dosen', NULL, NULL, NULL, NULL, NULL, 'disetujui', NULL, '2026-10-18 11:10:39', '2026-10-18 13:28:28'),
(15, 'Daniel Caesar', 'daniel.caesar.tgp25@stu.pnj.ac.id', '$2y$10$qTxpuqdhEhb5gkeIMJAfUuXSXbb8lK3XSXJ5xvsBiYFZJeWqaxWdG', 'mahasiswa', NULL, NULL, NULL, 'Singapura', NULL, 'ditolak', NULL, '2026-10-18 11:10:39', '2026-10-18 13:28:28'),
(16, 'Muhamad Sumbul', 'muhamad.sumbul.tgp25@stu.pnj.ac.id', '$2y$10$oeMJqLGWRSOxU65L4ArWge64slUDTnym54Pae1Tl9FnIGG7erwj0O', 'mahasiswa', NULL, NULL, NULL, 'Malaysia', NULL, 'disetujui', NULL, '2026-10-18 11:10:39', '2026-10-18 13:28:28'),
(17, 'Cheline Dior', 'cheline.dior.tgp25@stu.pnj.ac.id', '$2y$10$3eV0McbvroOlUuttO88tOOa8fkLFfAJZ8dSobruuAxddQDxCT59jq', 'mahasiswa', NULL, NULL, NULL, 'Inggris', NULL, 'disetujui', NULL, '2026-10-18 11:10:39', '2026-10-18 13:28:28'),
(127, 'Adelia Putri', 'adelia.putri.tik25@stu.pnj.ac.id', '$2y$10$EqhzJqMXhNJqSJotyDJznuCA25Pt4G6KOVJRQ47RthrdVUoqjWowC', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2025, 'disetujui', NULL, '2026-10-18 14:31:10', '2026-10-18 14:40:53'),
(128, 'Ahmad Fauzi', 'ahmad.fauzi.tik25@stu.pnj.ac.id', '$2y$10$PE9SMhU551lKOxMfpUttmePWhtDT1ETjEt0GOLeucD1cIM8trmb3a', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2026, 'selesai', NULL, '2026-10-18 14:31:10', '2026-10-18 14:40:53'),
(129, 'Aisyah Nurul', 'aisyah.nurul.tik25@stu.pnj.ac.id', '$2y$10$q3G7C/y1ffdeSk2zzc86aeaoK/cjRJn7N/LDCo/Kw3GP/6kPtCFPC', 'mahasiswa', NULL, NULL, NULL, 'Malaysia', 2025, 'ditolak', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(130, 'Alvin Pratama', 'alvin.pratama.tik25@stu.pnj.ac.id', '$2y$10$K6i.19gKkF5qwfetZkZpV.q.EYIko/yi25FNmR8XKjZT5zD31iV6W', 'mahasiswa', NULL, NULL, NULL, 'Korea Selatan', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(131, 'Amanda Sekar', 'amanda.sekar.tik25@stu.pnj.ac.id', '$2y$10$AgjAnJOM/AwkzrUm.Q/6euxR9NBmgN2nmq31p4pZOXuyInWFQ3rtO', 'mahasiswa', NULL, NULL, NULL, 'Jerman', 2025, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(132, 'Andika Wijaya', 'andika.wijaya.tik25@stu.pnj.ac.id', '$2y$10$d3..P5N3Q9fFbsCFi27hvuV5htCeN5unPitqFufNPFSQPUjYEZBUq', 'mahasiswa', NULL, NULL, NULL, 'Australia', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(133, 'Annisa Rahma', 'annisa.rahma.tik25@stu.pnj.ac.id', '$2y$10$xzd8OB6rCpWCV8V4BoIP5OvPIrPF4dv/ptoJ571qz/FUAxPClgN7e', 'mahasiswa', NULL, NULL, NULL, 'Inggris', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(134, 'Arya Kusuma', 'arya.kusuma.tik25@stu.pnj.ac.id', '$2y$10$JSUdkfgEDy3vN1pJaEwtEeGd4l23PLrkYwGRTXEHjg3Cv5xinWiRi', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2025, 'ditolak', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(135, 'Azzahra Siti', 'azzahra.siti.tik25@stu.pnj.ac.id', '$2y$10$8KMY193m3kjjxnlQKjbHcOnm2WOmts68J/FPUbeUQ5utWJ4ru6wQy', 'mahasiswa', NULL, NULL, NULL, 'Belanda', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(136, 'Bagas Satria', 'bagas.satria.tik25@stu.pnj.ac.id', '$2y$10$XYgD5YXdkMSxWQbT.0y9lurS0eBXj/P/Is3XacV2fTnshq4DLOaFq', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2025, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(137, 'Bayu Pamungkas', 'bayu.pamungkas.tik25@stu.pnj.ac.id', '$2y$10$rlDIhH8H2c0zpu7z25f7oe4SUtsQAp9DjVg4AVOdNDhPgpsn/r0qi', 'mahasiswa', NULL, NULL, NULL, 'Thailand', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:53'),
(138, 'Citra Lestari', 'citra.lestari.tik25@stu.pnj.ac.id', '$2y$10$zBz7NBTE9z2/f4oj1m9Z8.0vLWsubXOt7F8RxtvKLGMXWeGHJf1Bm', 'mahasiswa', NULL, NULL, NULL, 'Malaysia', 2025, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:54'),
(139, 'Daniel Sitorus', 'daniel.sitorus.tik25@stu.pnj.ac.id', '$2y$10$aFauHfcn2Aztw99DyIv8Ku10oEn9zh6.encbM8jYgcAr8RaEy6QHq', 'mahasiswa', NULL, NULL, NULL, 'Jerman', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:54'),
(140, 'Dion Yudha', 'dion.yudha.tik25@stu.pnj.ac.id', '$2y$10$FD48S0ol18CxHV0QIDLExOnm20VQiDR2CGlZ10wUZy3F.J8KphmFW', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:54'),
(141, 'Farhan Kurnia', 'farhan.kurnia.tik25@stu.pnj.ac.id', '$2y$10$KxYNZmbI2j/SfWr3FOfIP.2C3S0Mo0EDYbQ0KMStU.gMtfeVD3T3W', 'mahasiswa', NULL, NULL, NULL, 'Korea Selatan', 2025, 'ditolak', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:54'),
(142, 'Fiona Angelica', 'fiona.angelica.tik25@stu.pnj.ac.id', '$2y$10$ad.Vv6aUWg9LYCq0EI.FN..z2Za8tKhJclQ1v3taDrOkkR8RH8MIe', 'mahasiswa', NULL, NULL, NULL, 'Taiwan', 2026, 'selesai', NULL, '2026-10-18 14:31:11', '2026-10-18 14:40:54'),
(143, 'Gilang Ramadhan', 'gilang.ramadhan.tik25@stu.pnj.ac.id', '$2y$10$rX9LzIg.vmGaJg6/Bu5SIuOOVqe6/MUkDnIeHGQBsCyO91Rhdzvsu', 'mahasiswa', NULL, NULL, NULL, 'Australia', 2025, 'selesai', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(144, 'Hanifah Salsa', 'hanifah.salsa.tik25@stu.pnj.ac.id', '$2y$10$5vKpJy5mvqqEIHRk01PUweBQqSK9iuKWsIiz4fhQ4FErtXKagCaAi', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2026, 'selesai', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(145, 'Ikbal Maulana', 'ikbal.maulana.tik25@stu.pnj.ac.id', '$2y$10$nY7mH0IpU5K.cruR/9HoXOEXjHtDgOppwDwy4Klrj1K2UrIO5hFTi', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2025, 'selesai', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(146, 'Indra Laksana', 'indra.laksana.tik25@stu.pnj.ac.id', '$2y$10$6k2HTEya3UfUQDjQDm7SpuuoskhwB80NWwXAaSLpbJ4MSzIbn7dwC', 'mahasiswa', NULL, NULL, NULL, 'Belanda', 2026, 'ditolak', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(147, 'Kevin Sanjaya', 'kevin.sanjaya.tik25@stu.pnj.ac.id', '$2y$10$zCf5Gj36c/pNr6yWbPd.9.VCTARKDHNqOscmq2BbGkcDxoZy79Mii', 'mahasiswa', NULL, NULL, NULL, 'Malaysia', 2025, 'disetujui', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(148, 'Lutfi Hakim', 'lutfi.hakim.tik25@stu.pnj.ac.id', '$2y$10$8ZsRG7CJHL8z1yxmNAxWsu1pjD.DaHHKwhwi6l5vHQd5kw9ipWj1O', 'mahasiswa', NULL, NULL, NULL, 'Korea Selatan', 2026, '', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(149, 'Muhammad Rizky', 'muhammad.rizky.tik25@stu.pnj.ac.id', '$2y$10$nyIliODePTAzsjc9bLwUdeMDvo3wzEstZTSRAa5VWDku.Bk8l9wxm', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2026, 'disetujui', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(150, 'Nadia Utami', 'nadia.utami.tik25@stu.pnj.ac.id', '$2y$10$XygBizP7UDRC4pdYlh4hDOld6fLNqzPxaIdqnAFUir8pcGf8DSfZO', 'mahasiswa', NULL, NULL, NULL, 'Singapura', 2025, 'ditolak', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(151, 'Naufal Hidayat', 'naufal.hidayat.tik25@stu.pnj.ac.id', '$2y$10$7JLwV4MKqnNSU2ixmixYr.sOnqOpI1qS70QTOp5EqXobbjaEBLL4K', 'mahasiswa', NULL, NULL, NULL, 'Jerman', 2026, '', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:54'),
(152, 'Putri Syifa', 'putri.syifa.tik25@stu.pnj.ac.id', '$2y$10$J9PvDy0pMtYSn3Q89FRU0.uJVQRQRCDSxFwqhpwAAzDBf7ue2vA06', 'mahasiswa', NULL, NULL, NULL, 'Inggris', 2025, 'disetujui', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:55'),
(153, 'Raditya Pradipta', 'raditya.pradipta.tik25@stu.pnj.ac.id', '$2y$10$PBfmhDrAaQTH.HmGsJF6I.F3eJxqkXbEE3J4BrLzIzE95uuQNJxSO', 'mahasiswa', NULL, NULL, NULL, 'Australia', 2026, 'selesai', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:55'),
(154, 'Rafi Akbar', 'rafi.akbar.tik25@stu.pnj.ac.id', '$2y$10$dBPwr.J0JOKfOqI1M915IuLFj.4j1Qxf0TaMBgyXWrQevtJWZOTn6', 'mahasiswa', NULL, NULL, NULL, 'Thailand', 2025, 'disetujui', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:55'),
(155, 'Salsa Bila', 'salsa.bila.tik25@stu.pnj.ac.id', '$2y$10$84MVu/FHt49oWERUrkaEu.zX9oIqKxqPhb4IfmNANE23ssTHWlRCu', 'mahasiswa', NULL, NULL, NULL, 'Vietnam', 2026, '', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:55'),
(156, 'Tariq Ziyad', 'tariq.ziyad.tik25@stu.pnj.ac.id', '$2y$10$1HOCMLGrJEg5BBhqPi.hnO6fL/pHZzJe/ONkoWoeevBtiJcqWjduW', 'mahasiswa', NULL, NULL, NULL, 'Jepang', 2026, 'disetujui', NULL, '2026-10-18 14:31:12', '2026-10-18 14:40:55');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dokumen_pdln`
--
ALTER TABLE `dokumen_pdln`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indeks untuk tabel `pengajuan_pdln`
--
ALTER TABLE `pengajuan_pdln`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `dokumen_pdln`
--
ALTER TABLE `dokumen_pdln`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_pdln`
--
ALTER TABLE `pengajuan_pdln`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumen_pdln`
--
ALTER TABLE `dokumen_pdln`
  ADD CONSTRAINT `dokumen_pdln_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_pdln` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajuan_pdln`
--
ALTER TABLE `pengajuan_pdln`
  ADD CONSTRAINT `pengajuan_pdln_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
