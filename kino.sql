-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 22, 2025 at 09:42 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kino`
--
CREATE DATABASE IF NOT EXISTS `kino` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `kino`;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `logged_in_users`
--

CREATE TABLE `logged_in_users` (
  `sessionId` varchar(100) NOT NULL,
  `userId` int(11) NOT NULL,
  `lastUpdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `powiadomienia`
--

CREATE TABLE `powiadomienia` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `powiadomienia`
--

INSERT INTO `powiadomienia` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(5, 5, 'Promocja z kodem KINO20 20% na wszytskie seanse', 1, '2025-01-22 15:13:06');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `movie_title` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `ticket_count` int(11) NOT NULL,
  `ticket_type` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `name`, `email`, `phone`, `movie_title`, `date`, `time`, `ticket_count`, `ticket_type`, `created_at`) VALUES
(5, 'Agnieszka Jakubowska', 'agnes@wp.pl', '123456789', 'I saw the TV glow', '2025-01-22', '13:25:00', 1, 'ulgowy', '2025-01-22 15:18:18');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `row_number` int(11) NOT NULL,
  `seat_number` int(11) NOT NULL,
  `movie_title` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `ticket_type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seats`
--

INSERT INTO `seats` (`id`, `reservation_id`, `row_number`, `seat_number`, `movie_title`, `date`, `time`, `ticket_type`) VALUES
(12, 5, 7, 9, 'I saw the TV glow', '2025-01-22', '13:25:00', 'ulgowy');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `userName` varchar(100) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `userName`, `fullName`, `email`, `passwd`, `status`, `date`) VALUES
(5, 'Agnieszka', 'Agnieszka Jakubowska', 'agnes@wp.pl', '$2y$10$g9b9WVhsPSXRqQWJjkbeZOu2ilZXyLaPSzymqeOodzs.B6gJ4Ihue', 1, '2025-01-21 00:00:00'),
(8, 'Admin', 'Adrian Kowalski', 'adko@gmail.com', '$2y$10$muIshDamI7rI2wX7.mE8keQvuog8R.75/Dicje/FscfTipKkiMNe.', 2, '2025-01-22 00:00:00');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `logged_in_users`
--
ALTER TABLE `logged_in_users`
  ADD PRIMARY KEY (`sessionId`);

--
-- Indeksy dla tabeli `powiadomienia`
--
ALTER TABLE `powiadomienia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName` (`userName`,`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `powiadomienia`
--
ALTER TABLE `powiadomienia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `powiadomienia`
--
ALTER TABLE `powiadomienia`
  ADD CONSTRAINT `powiadomienia_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seats`
--
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
