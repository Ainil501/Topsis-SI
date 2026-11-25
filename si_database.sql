-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 21, 2026 at 04:31 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `si_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `tabel_bobot`
--

CREATE TABLE `tabel_bobot` (
  `id_bobot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bpendidikan` int NOT NULL,
  `bpengalaman` int NOT NULL,
  `bskill` int NOT NULL,
  `busia` int NOT NULL,
  `bkarakter` int NOT NULL,
  `periode_bobot` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabel_daftar`
--

CREATE TABLE `tabel_daftar` (
  `id_daftar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_pekerja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabel_nilai`
--

CREATE TABLE `tabel_nilai` (
  `id_nilai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_bobot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_daftar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `r_pendidikan` decimal(10,6) DEFAULT NULL,
  `r_pengalaman` decimal(10,6) DEFAULT NULL,
  `r_skill` decimal(10,6) DEFAULT NULL,
  `r_usia` decimal(10,6) DEFAULT NULL,
  `r_karakter` decimal(10,6) DEFAULT NULL,
  `y_pendidikan` decimal(10,6) DEFAULT NULL,
  `y_pengalaman` decimal(10,6) DEFAULT NULL,
  `y_skill` decimal(10,6) DEFAULT NULL,
  `y_usia` decimal(10,6) DEFAULT NULL,
  `y_karakter` decimal(10,6) DEFAULT NULL,
  `d_positif` decimal(10,6) DEFAULT NULL,
  `d_negatif` decimal(10,6) DEFAULT NULL,
  `preferensi` decimal(10,6) DEFAULT NULL,
  `ranking` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabel_pekerja`
--

CREATE TABLE `tabel_pekerja` (
  `id_pekerja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pekerja` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pendidikan` int NOT NULL,
  `pengalaman` int NOT NULL,
  `skill` int NOT NULL,
  `usia` int NOT NULL,
  `karakter` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tabel_bobot`
--
ALTER TABLE `tabel_bobot`
  ADD PRIMARY KEY (`id_bobot`);

--
-- Indexes for table `tabel_daftar`
--
ALTER TABLE `tabel_daftar`
  ADD PRIMARY KEY (`id_daftar`),
  ADD KEY `fk_daftar_pekerja` (`id_pekerja`);

--
-- Indexes for table `tabel_nilai`
--
ALTER TABLE `tabel_nilai`
  ADD PRIMARY KEY (`id_nilai`),
  ADD UNIQUE KEY `uq_bobot_daftar` (`id_bobot`,`id_daftar`),
  ADD KEY `fk_nilai_bobot` (`id_bobot`),
  ADD KEY `fk_nilai_daftar` (`id_daftar`);

--
-- Indexes for table `tabel_pekerja`
--
ALTER TABLE `tabel_pekerja`
  ADD PRIMARY KEY (`id_pekerja`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tabel_daftar`
--
ALTER TABLE `tabel_daftar`
  ADD CONSTRAINT `fk_daftar_pekerja` FOREIGN KEY (`id_pekerja`) REFERENCES `tabel_pekerja` (`id_pekerja`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tabel_nilai`
--
ALTER TABLE `tabel_nilai`
  ADD CONSTRAINT `fk_nilai_bobot` FOREIGN KEY (`id_bobot`) REFERENCES `tabel_bobot` (`id_bobot`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_daftar` FOREIGN KEY (`id_daftar`) REFERENCES `tabel_daftar` (`id_daftar`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
