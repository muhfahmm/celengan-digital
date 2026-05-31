-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 01:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_celengan`
--

-- --------------------------------------------------------

--
-- Table structure for table `celengan`
--

CREATE TABLE `celengan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_celengan` varchar(150) NOT NULL,
  `target` int(11) DEFAULT 0,
  `pengisian` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'harian',
  `total` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `celengan`
--

INSERT INTO `celengan` (`id`, `user_id`, `nama_celengan`, `target`, `pengisian`, `total`, `created_at`, `is_pinned`) VALUES
(28, 1, 'cash', 20000000, 'harian', 550000, '2025-11-17 11:42:55', 1),
(29, 1, 'gopay', 5000000, 'harian', 0, '2025-11-19 11:58:20', 1),
(30, 1, 'brimo', 10000000, 'harian', 48000, '2025-11-19 11:58:34', 0),
(31, 1, 'dana', 5000000, 'harian', 932000, '2025-11-20 14:14:48', 1),
(33, 1, 'emas', 15000000, 'harian', 2547081, '2025-11-23 08:57:11', 0);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `celengan_id` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `tipe` enum('masuk','keluar') NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `celengan_id`, `nominal`, `tipe`, `keterangan`, `tanggal`) VALUES
(315, 28, 1800000, 'masuk', '', '2025-07-19'),
(316, 28, 50000, 'keluar', '', '2025-07-21'),
(317, 28, 915000, 'masuk', '', '2025-07-27'),
(318, 28, 35000, 'masuk', '', '2025-08-26'),
(319, 28, 350000, 'masuk', '', '2025-08-26'),
(320, 28, 300000, 'masuk', '', '2025-08-30'),
(321, 28, 1750000, 'keluar', 'jajan NB', '2025-08-30'),
(322, 28, 300000, 'masuk', '', '2025-08-31'),
(323, 28, 100000, 'masuk', '', '2025-08-31'),
(324, 28, 500000, 'masuk', '', '2025-08-31'),
(325, 28, 170000, 'masuk', '', '2025-09-05'),
(326, 28, 25000, 'masuk', '', '2025-09-06'),
(327, 28, 850000, 'masuk', '', '2025-09-06'),
(328, 28, 30000, 'masuk', '', '2025-09-07'),
(329, 28, 50000, 'masuk', '', '2025-09-07'),
(330, 28, 30000, 'masuk', '', '2025-09-08'),
(331, 28, 100000, 'masuk', '', '2025-09-09'),
(332, 28, 35000, 'masuk', '', '2025-09-10'),
(333, 28, 60000, 'masuk', '', '2025-09-10'),
(335, 28, 320000, 'masuk', 'gopay', '2025-09-11'),
(336, 28, 155000, 'masuk', '', '2025-09-11'),
(349, 28, 300000, 'keluar', '', '2025-09-12'),
(350, 28, 300000, 'masuk', '', '2025-09-12'),
(351, 28, 35000, 'masuk', '', '2025-09-12'),
(352, 28, 50000, 'masuk', '', '2025-09-12'),
(353, 28, 250000, 'masuk', '', '2025-09-12'),
(354, 28, 35000, 'masuk', '', '2025-09-13'),
(355, 28, 75000, 'masuk', '', '2025-09-13'),
(356, 28, 30000, 'masuk', '', '2025-09-13'),
(357, 28, 10000, 'masuk', '', '2025-09-13'),
(358, 28, 105000, 'masuk', '', '2025-09-15'),
(359, 28, 200000, 'masuk', '', '2025-09-15'),
(360, 28, 100000, 'masuk', '', '2025-09-15'),
(361, 28, 100000, 'masuk', 'gopay', '2025-09-16'),
(362, 28, 35000, 'masuk', '', '2025-09-16'),
(363, 28, 80000, 'masuk', '', '2025-09-16'),
(364, 28, 290000, 'masuk', '', '2025-09-16'),
(365, 28, 300000, 'keluar', 'gopay', '2025-09-17'),
(366, 28, 300000, 'masuk', '', '2025-09-17'),
(367, 28, 40000, 'masuk', '', '2025-09-17'),
(368, 28, 30000, 'masuk', '', '2025-09-17'),
(369, 28, 10000, 'masuk', '', '2025-09-17'),
(370, 28, 20000, 'masuk', '', '2025-09-17'),
(371, 28, 10000, 'keluar', '', '2025-09-17'),
(372, 28, 90000, 'masuk', '', '2025-09-18'),
(373, 28, 200000, 'masuk', 'gopay', '2025-09-19'),
(374, 28, 50000, 'keluar', 'jajan stik', '2025-09-19'),
(375, 28, 200000, 'masuk', 'gopay', '2025-09-20'),
(376, 28, 250000, 'masuk', '', '2025-09-21'),
(377, 28, 200000, 'masuk', 'gopay', '2025-09-21'),
(378, 28, 10000, 'keluar', '', '2025-09-21'),
(379, 28, 50000, 'masuk', 'gopay', '2025-09-22'),
(380, 28, 50000, 'masuk', '', '2025-09-22'),
(381, 28, 200000, 'masuk', 'gopay', '2025-09-22'),
(382, 28, 30000, 'masuk', '', '2025-09-23'),
(383, 28, 30000, 'masuk', '', '2025-09-23'),
(384, 28, 200000, 'masuk', '', '2025-09-23'),
(385, 28, 290000, 'masuk', 'gopay', '2025-09-24'),
(386, 28, 40000, 'keluar', 'gopay', '2025-09-25'),
(387, 28, 46000, 'keluar', 'gacoan', '2025-09-25'),
(388, 28, 20000, 'masuk', '', '2025-09-25'),
(389, 28, 322000, 'masuk', '', '2025-09-26'),
(390, 28, 50000, 'masuk', '', '2025-09-26'),
(391, 28, 50000, 'masuk', '', '2025-09-28'),
(392, 28, 550000, 'masuk', '', '2025-09-28'),
(393, 28, 200000, 'masuk', 'gopay', '2025-09-29'),
(394, 28, 268000, 'masuk', '', '2025-09-29'),
(395, 28, 44000, 'keluar', 'gacoan', '2025-09-30'),
(396, 28, 362000, 'masuk', '', '2025-10-01'),
(397, 28, 6000, 'keluar', '', '2025-10-02'),
(398, 28, 220000, 'masuk', '', '2025-10-02'),
(399, 28, 200000, 'masuk', '', '2025-10-02'),
(400, 28, 41000, 'keluar', 'topup ajaib', '2025-10-04'),
(401, 28, 200000, 'masuk', '', '2025-10-04'),
(402, 28, 295000, 'masuk', '', '2025-10-04'),
(403, 28, 600000, 'masuk', '', '2025-10-06'),
(404, 28, 200000, 'masuk', '', '2025-10-06'),
(405, 28, 4000, 'keluar', '', '2025-10-06'),
(406, 28, 487000, 'masuk', '', '2025-10-09'),
(407, 28, 338000, 'masuk', '', '2025-10-11'),
(408, 28, 200000, 'masuk', 'gopay', '2025-10-12'),
(409, 28, 200000, 'masuk', 'gopay', '2025-10-12'),
(410, 28, 2000000, 'keluar', 'jajan NB', '2025-10-13'),
(411, 28, 250000, 'masuk', 'gopay', '2025-10-13'),
(412, 28, 30000, 'keluar', 'jajan', '2025-10-14'),
(413, 28, 108000, 'keluar', 'beli buku', '2025-10-14'),
(414, 28, 36000, 'keluar', 'gacoan', '2025-10-15'),
(415, 28, 357000, 'masuk', '', '2025-10-15'),
(416, 28, 200000, 'masuk', 'gopay', '2025-10-16'),
(417, 28, 200000, 'keluar', 'topup ajaib', '2025-10-17'),
(418, 28, 200000, 'masuk', 'gopay', '2025-10-17'),
(419, 28, 100000, 'masuk', 'gopay', '2025-10-18'),
(420, 28, 200000, 'masuk', 'gopay', '2025-10-19'),
(421, 28, 100000, 'keluar', 'topup pluang', '2025-10-20'),
(422, 28, 50000, 'keluar', 'topup pluang', '2025-10-21'),
(423, 28, 150000, 'keluar', 'topup pluang', '2025-10-21'),
(424, 28, 250000, 'masuk', '', '2025-10-21'),
(425, 28, 4000, 'keluar', '', '2025-10-23'),
(426, 28, 600000, 'masuk', '', '2025-10-23'),
(427, 28, 200000, 'masuk', '', '2025-10-23'),
(428, 28, 100000, 'keluar', 'topup pluang', '2025-10-23'),
(429, 28, 50000, 'keluar', 'topup pluang', '2025-10-24'),
(430, 28, 200000, 'masuk', 'gopay', '2025-10-24'),
(431, 28, 200000, 'masuk', '', '2025-10-24'),
(432, 28, 100000, 'keluar', 'topup pluang', '2025-10-24'),
(433, 28, 300000, 'masuk', '', '2025-10-25'),
(434, 28, 100000, 'keluar', 'topup pluang', '2025-10-25'),
(435, 28, 100000, 'keluar', 'topup pluang', '2025-10-25'),
(436, 28, 250000, 'masuk', 'gopay', '2025-10-28'),
(437, 28, 200000, 'masuk', '', '2025-10-29'),
(438, 28, 1540000, 'masuk', '', '2025-11-01'),
(439, 28, 300000, 'masuk', '', '2025-11-02'),
(440, 28, 1000000, 'keluar', 'jajan keyboard', '2025-11-02'),
(441, 28, 595000, 'masuk', '', '2025-11-04'),
(442, 28, 600000, 'keluar', 'topup ajaib', '2025-11-05'),
(443, 28, 600000, 'masuk', '', '2025-11-06'),
(444, 28, 1068000, 'masuk', '', '2025-11-10'),
(445, 28, 1172684, 'keluar', 'jajan jaket eiger', '2025-11-16'),
(446, 28, 300000, 'masuk', '', '2025-11-16'),
(447, 28, 722905, 'masuk', '', '2025-11-19'),
(452, 29, 50000, 'masuk', '', '2025-11-20'),
(453, 29, 250000, 'masuk', '', '2025-11-20'),
(454, 31, 200000, 'masuk', '', '2025-11-20'),
(455, 31, 25000, 'keluar', '', '2025-11-21'),
(456, 28, 97000, 'masuk', '', '2025-11-21'),
(462, 29, 200000, 'masuk', '', '2025-11-21'),
(463, 29, 500000, 'keluar', '', '2025-11-22'),
(464, 31, 175000, 'keluar', '', '2025-11-22'),
(469, 28, 960000, 'masuk', '', '2025-11-22'),
(472, 29, 100000, 'masuk', '', '2025-11-23'),
(473, 29, 100000, 'keluar', '', '2025-11-23'),
(474, 33, 78220, 'masuk', '', '2025-10-19'),
(475, 33, 25070, 'masuk', '', '2025-10-20'),
(476, 33, 49139, 'masuk', '', '2025-10-21'),
(477, 33, 48134, 'masuk', '', '2025-10-23'),
(478, 33, 49388, 'masuk', '', '2025-10-24'),
(479, 33, 30087, 'masuk', '', '2025-10-25'),
(480, 33, 121638, 'masuk', '', '2025-10-25'),
(481, 33, 351389, 'keluar', '', '2025-10-26'),
(482, 33, 10030, 'masuk', '', '2025-10-27'),
(483, 33, 49138, 'masuk', '', '2025-10-27'),
(484, 33, 25070, 'masuk', '', '2025-10-29'),
(485, 33, 300833, 'masuk', '', '2025-10-29'),
(486, 33, 66385, 'masuk', '', '2025-10-29'),
(487, 33, 596654, 'masuk', '', '2025-10-29'),
(488, 33, 74858, 'masuk', '', '2025-10-30'),
(489, 33, 160045, 'masuk', '', '2025-11-02'),
(490, 33, 134375, 'masuk', '', '2025-11-02'),
(491, 33, 98473, 'masuk', '', '2025-11-03'),
(492, 33, 55153, 'masuk', '', '2025-11-04'),
(493, 33, 49598, 'masuk', '', '2025-11-05'),
(494, 33, 49177, 'masuk', '', '2025-11-07'),
(495, 33, 286165, 'masuk', '', '2025-11-21'),
(496, 33, 98334, 'masuk', '', '2025-11-23'),
(499, 28, 10000, 'masuk', '', '2025-11-23'),
(500, 28, 100000, 'masuk', '', '2025-11-23'),
(501, 28, 179779, 'masuk', '', '2025-11-24'),
(503, 29, 400000, 'masuk', '', '2025-11-25'),
(504, 29, 400000, 'keluar', '', '2025-11-26'),
(505, 28, 50000, 'masuk', '', '2025-11-26'),
(509, 33, 49168, 'masuk', '', '2025-11-24'),
(510, 33, 393338, 'masuk', '', '2025-11-25'),
(511, 29, 200000, 'masuk', '', '2025-11-27'),
(512, 29, 201000, 'masuk', '', '2025-11-28'),
(513, 30, 48000, 'masuk', '', '2025-11-28'),
(514, 28, 400000, 'masuk', '', '2025-11-28'),
(516, 29, 83000, 'keluar', '', '2025-11-29'),
(517, 28, 70000, 'masuk', '', '2025-11-29'),
(519, 28, 11950000, 'keluar', 'jajan iphong 15', '2025-11-30'),
(520, 29, 200000, 'masuk', '', '2025-11-30'),
(521, 29, 118000, 'keluar', '', '2025-12-01'),
(522, 29, 200000, 'masuk', '', '2025-12-04'),
(523, 29, 200000, 'masuk', '', '2025-12-05'),
(524, 28, 800000, 'keluar', 'ilang/nyangkut di atm', '2025-12-05'),
(525, 28, 200000, 'masuk', '', '2025-12-06'),
(526, 28, 50000, 'masuk', '', '2025-12-07'),
(527, 29, 336000, 'keluar', 'beli softcase + thempered glass', '2025-12-07'),
(528, 29, 388000, 'keluar', 'beli game', '2025-12-07'),
(529, 29, 200000, 'masuk', '', '2025-12-07'),
(530, 29, 176000, 'keluar', '', '2025-12-08'),
(531, 29, 2000000, 'masuk', '', '2025-12-09'),
(532, 29, 1500000, 'masuk', '', '2025-12-09'),
(533, 28, 3500000, 'keluar', '', '2025-12-09'),
(534, 29, 2700000, 'keluar', 'jajan monitor', '2025-12-12'),
(535, 29, 135000, 'keluar', 'jajan softcase + tempered', '2025-12-12'),
(536, 29, 2050000, 'masuk', '', '2025-12-14'),
(538, 29, 2777000, 'keluar', 'jajan kursi ergo', '2025-12-14'),
(539, 29, 200000, 'masuk', '', '2025-12-18'),
(540, 29, 238000, 'keluar', '', '2025-12-24'),
(541, 28, 70000, 'keluar', '', '2026-01-10'),
(542, 31, 200000, 'masuk', '', '2025-12-22'),
(543, 31, 55000, 'masuk', '', '2025-12-24'),
(544, 31, 570000, 'masuk', '', '2025-12-25'),
(545, 31, 100000, 'masuk', '', '2025-12-28'),
(546, 31, 150000, 'masuk', '', '2026-01-01'),
(547, 31, 150000, 'masuk', '', '2026-01-10'),
(548, 31, 100000, 'masuk', '', '2026-01-03'),
(549, 31, 150000, 'masuk', '', '2026-01-04'),
(550, 31, 50000, 'masuk', '', '2026-01-06'),
(551, 31, 100000, 'masuk', '', '2026-01-07'),
(552, 31, 150000, 'masuk', '', '2026-01-09'),
(553, 31, 200000, 'masuk', '', '2026-01-10'),
(554, 31, 200000, 'keluar', '', '2025-12-22'),
(555, 31, 55000, 'keluar', '', '2025-12-24'),
(556, 31, 570000, 'keluar', '', '2025-12-25'),
(557, 31, 100000, 'keluar', '', '2026-01-10'),
(558, 31, 7500, 'keluar', '', '2026-01-06'),
(559, 31, 17500, 'keluar', '', '2026-01-07'),
(560, 31, 10000, 'keluar', '', '2026-01-08'),
(562, 31, 120000, 'keluar', '', '2026-01-09'),
(563, 31, 200000, 'keluar', '', '2026-01-10'),
(564, 31, 9000, 'keluar', '', '2026-01-10'),
(565, 31, 199000, 'masuk', '', '2026-01-10'),
(567, 28, 100000, 'keluar', '', '2026-01-11'),
(568, 31, 200000, 'masuk', '', '2026-01-12'),
(569, 31, 9000, 'keluar', '', '2026-01-12'),
(570, 31, 144000, 'keluar', '', '2026-01-14');

--
-- Triggers `transaksi`
--
DELIMITER $$
CREATE TRIGGER `transaksi_after_delete` AFTER DELETE ON `transaksi` FOR EACH ROW BEGIN
    UPDATE celengan
    SET total = COALESCE((
        SELECT SUM(
            CASE 
                WHEN tipe = 'masuk' THEN nominal
                WHEN tipe = 'keluar' THEN -nominal
            END
        )
        FROM transaksi
        WHERE celengan_id = OLD.celengan_id
    ), 0)
    WHERE id = OLD.celengan_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'fahiim', '$2y$10$VN7kDWdrZYBAnFP1AQZMSeo1.7s7jHzmXdsbGJa7axSHF.9B/JsdC', '2025-10-22 10:30:58'),
(2, 'admin', '$2y$10$esYE/DeE7nl0.A5m.30rUuu7TehPb.IR2s9A8h7NhXJtPuuDyHR1u', '2025-11-23 04:01:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `celengan`
--
ALTER TABLE `celengan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_pinned` (`user_id`,`is_pinned`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_ibfk_1` (`celengan_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `celengan`
--
ALTER TABLE `celengan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=572;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `celengan`
--
ALTER TABLE `celengan`
  ADD CONSTRAINT `celengan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`celengan_id`) REFERENCES `celengan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
