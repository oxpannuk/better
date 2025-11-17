-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 17 2025 г., 00:14
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `name` varchar(150) NOT NULL,
  `city_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `companies`
--

INSERT INTO `companies` (`id`, `name`, `city_id`) VALUES
(1, 'ПАО \"НК \"РОСНЕФТЬ\"', 1),
(2, 'ПАО \"ЛУКОЙЛ\"', 1),
(3, 'ОАО \"РЖД\"', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`, `parent_id`, `upvotes`, `downvotes`, `city_id`, `company_id`, `type_id`, `office_id`) VALUES
(12, 4, 'привет ставьте лайки', '2025-11-16 15:59:25', NULL, 2, 0, NULL, NULL, NULL, NULL),
(28, 1, 'приветы', '2025-11-16 22:23:22', NULL, 2, 0, 1, 2, 1, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `message_votes`
--

CREATE TABLE `message_votes` (
  `id` int NOT NULL,
  `message_id` int NOT NULL,
  `user_id` int NOT NULL,
  `vote` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `message_votes`
--

INSERT INTO `message_votes` (`id`, `message_id`, `user_id`, `vote`) VALUES
(86, 12, 4, 1),
(88, 28, 4, 1),
(93, 12, 1, 1),
(99, 28, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `offices`
--

CREATE TABLE `offices` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `company_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `offices`
--

INSERT INTO `offices` (`id`, `name`, `address`, `company_id`) VALUES
(1, 'Головной офис', 'ул. Тверская, 10, Москва', 1),
(2, 'IT-отдел', 'пр. Мира, 25, Москва', 1),
(3, 'Филиал СПб', 'Невский пр., 15, Санкт-Петербург', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `suggestion_types`
--

CREATE TABLE `suggestion_types` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `role`) VALUES
(1, 'user', '$2y$10$m2grdLzn0meOYwiIcOdf4uLfu7AfqAd0fHg5JUyuWM1HRZaS6Ihuq', '2025-11-15 21:04:49', 'user'),
(2, 'jaja', '$2y$10$YsvvU.nIzEiuD0yPl8dwn.yL6ItL11fOFrahr66PxjjkL/ZLmb/QC', '2025-11-15 21:06:27', 'user'),
(3, 'jajaja', '$2y$10$XTX7RJRGGsXyh3JIGBdajOhKsxVPUekrFxd15r8JbiXLcKH6GV6LK', '2025-11-15 21:10:18', 'user'),
(4, 'admin', '$2y$10$ddn.dWe/SNE/Cw8ziGgRR.FqSaWkq5kNYoPT76HuR1jdoGycg.YLS', '2025-11-16 15:55:21', 'admin'),
(5, 'сеня', '$2y$10$h3eVc3reAvurlDhiwFxGoueEOVj4EdCP5f/hQnlg1f.u83PEKTXd2', '2025-11-16 23:42:30', 'user');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT для таблицы `message_votes`
--
ALTER TABLE `message_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT для таблицы `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `suggestion_types`
--
ALTER TABLE `suggestion_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `offices_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
