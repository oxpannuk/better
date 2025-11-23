-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 22 2025 г., 01:03
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `guestbook_auth`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cities`
--

CREATE TABLE `cities` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `cities`
--

INSERT INTO `cities` (`id`, `name`) VALUES
(3, 'Казань'),
(1, 'Москва'),
(4, 'Новосибирск'),
(5, 'Омск'),
(2, 'Санкт-Петербург');

-- --------------------------------------------------------

--
-- Структура таблицы `companies`
--

CREATE TABLE `companies` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `companies`
--

INSERT INTO `companies` (`id`, `name`) VALUES
(1, 'ПАО \"НК \"РОСНЕФТЬ\"'),
(2, 'ПАО \"ЛУКОЙЛ\"'),
(3, 'ОАО \"РЖД\"');

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL,
  `upvotes` int DEFAULT '0',
  `downvotes` int DEFAULT '0',
  `city_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `office_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`, `parent_id`, `upvotes`, `downvotes`, `city_id`, `company_id`, `type_id`, `office_id`) VALUES
(12, 4, 'привет ставьте лайки!ыыц1', '2025-11-16 15:59:25', NULL, 4, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `message_votes`
--

CREATE TABLE `message_votes` (
  `id` int NOT NULL,
  `message_id` int NOT NULL,
  `user_id` int NOT NULL,
  `vote` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `message_votes`
--

INSERT INTO `message_votes` (`id`, `message_id`, `user_id`, `vote`) VALUES
(86, 12, 4, 1),
(101, 12, 6, 1),
(159, 12, 1, 1),
(177, 12, 7, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `offices`
--

CREATE TABLE `offices` (
  `id` int NOT NULL,
  `address` text NOT NULL,
  `company_id` int DEFAULT NULL,
  `city_id` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `offices`
--

INSERT INTO `offices` (`id`, `address`, `company_id`, `city_id`) VALUES
(4, 'ул. Новый Арбат, д. 36', 1, 1),
(5, 'ул. Тверская, д. 12', 1, 1),
(6, 'Ленинградский проспект, д. 39, стр. 1', 1, 1),
(7, 'Кутузовский проспект, д. 32', 1, 1),
(8, 'Невский проспект, д. 85', 1, 2),
(9, 'Московский проспект, д. 212', 1, 2),
(10, 'ул. Баумана, д. 44', 1, 3),
(11, 'Красный проспект, д. 77', 1, 4),
(12, 'ул. Ленина, д. 10', 1, 5),
(13, 'ул. Профсоюзная, д. 156', 2, 1),
(14, 'Ленинский проспект, д. 95', 2, 1),
(15, 'ул. Большая Якиманка, д. 23', 2, 1),
(16, 'Варшавское шоссе, д. 118, к. 1', 2, 1),
(17, 'ул. Большая Морская, д. 37', 2, 2),
(18, 'Лиговский проспект, д. 153', 2, 2),
(19, 'ул. Кремлёвская, д. 18', 2, 3),
(20, 'ул. Петербургская, д. 52', 2, 3),
(21, 'ул. Ленина, д. 56', 2, 4),
(22, 'ул. Советская, д. 18', 2, 4),
(23, 'проспект Карла Маркса, д. 22', 2, 5),
(24, 'Комсомольская площадь, д. 3', 3, 1),
(25, 'ул. Земляной Вал, д. 29', 3, 1),
(26, 'ул. Новорязанская, д. 12', 3, 1),
(27, 'Невский проспект, д. 85', 3, 2),
(28, 'пл. Восстания, д. 2', 3, 2),
(29, 'Привокзальная площадь, д. 1', 3, 3),
(30, 'ул. Дмитрия Шамшурина, д. 43', 3, 4),
(31, 'ул. Леконта, д. 2', 3, 5),
(32, 'Пресненская набережная, д. 2', 3, 1),
(33, 'ул. Политехническая, д. 25', 3, 2),
(34, 'ул. Масленникова, 70', 2, 5);

-- --------------------------------------------------------

--
-- Структура таблицы `suggestion_types`
--

CREATE TABLE `suggestion_types` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `suggestion_types`
--

INSERT INTO `suggestion_types` (`id`, `name`) VALUES
(1, 'Предложение'),
(2, 'Жалоба');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `role`) VALUES
(1, 'user', '$2y$10$m2grdLzn0meOYwiIcOdf4uLfu7AfqAd0fHg5JUyuWM1HRZaS6Ihuq', '2025-11-15 21:04:49', 'user'),
(2, 'jaja', '$2y$10$YsvvU.nIzEiuD0yPl8dwn.yL6ItL11fOFrahr66PxjjkL/ZLmb/QC', '2025-11-15 21:06:27', 'user'),
(3, 'jajaja', '$2y$10$XTX7RJRGGsXyh3JIGBdajOhKsxVPUekrFxd15r8JbiXLcKH6GV6LK', '2025-11-15 21:10:18', 'user'),
(4, 'admin', '$2y$10$ddn.dWe/SNE/Cw8ziGgRR.FqSaWkq5kNYoPT76HuR1jdoGycg.YLS', '2025-11-16 15:55:21', 'admin'),
(5, 'сеня', '$2y$10$h3eVc3reAvurlDhiwFxGoueEOVj4EdCP5f/hQnlg1f.u83PEKTXd2', '2025-11-16 23:42:30', 'user'),
(6, 'ghbdtn', '$2y$10$yMYEiCAp0inQuKNb57CR5uH4J55eQ0BxfPVranbqRs/U9XBd7Qw0G', '2025-11-17 13:15:31', 'user'),
(7, 'gaga', '$2y$10$Hh.B8zAIYURaRWJU6cW1SuEb1dQEtbZP4hRad6k8AZ2GE5DtQtG62', '2025-11-21 23:28:58', 'user');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Индексы таблицы `message_votes`
--
ALTER TABLE `message_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`message_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_offices_city` (`city_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Индексы таблицы `suggestion_types`
--
ALTER TABLE `suggestion_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `message_votes`
--
ALTER TABLE `message_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=184;

--
-- AUTO_INCREMENT для таблицы `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `suggestion_types`
--
ALTER TABLE `suggestion_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_5` FOREIGN KEY (`type_id`) REFERENCES `suggestion_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_6` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `message_votes`
--
ALTER TABLE `message_votes`
  ADD CONSTRAINT `message_votes_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `fk_offices_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_offices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
