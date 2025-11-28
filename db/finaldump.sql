-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 28 2025 г., 13:45
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
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
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
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL
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
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
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
(12, 4, 'короче, читы - бан\nсообщение не по делу - бан\nоскорбления - бан\nоскорбление администрации - расстрел, потом бан', '2025-11-16 15:59:25', NULL, 9, 0, NULL, NULL, NULL, NULL),
(71, 1, 'Предлагаю сделать работникам офиса бесплатный доступ к заправке автомобиля, но в пределах разумного!', '2025-11-28 13:02:56', NULL, 8, 0, 5, 2, 1, 23),
(72, 8, 'полностью согласен, было бы неплохо сделать еще бесплатный обед, было бы 👍 ВО!', '2025-11-28 13:07:52', 71, 5, 0, NULL, NULL, NULL, NULL),
(73, 9, 'ну обед уже слишком а топлива нам не жалко для работяг', '2025-11-28 13:12:14', 71, 4, 1, NULL, NULL, NULL, NULL),
(74, 9, 'ну ладно возьмите пончики 🍩🍩🍩🍩🍩🍩', '2025-11-28 13:12:51', 71, 5, 0, NULL, NULL, NULL, NULL),
(75, 10, 'эээ ну мне тут ваще не нрав и полный отстой тут работать крысы бегают и тд 👎👎👎', '2025-11-28 13:14:53', NULL, 0, 8, 1, 1, 2, 4),
(76, 10, 'не', '2025-11-28 13:15:38', 12, 0, 6, NULL, NULL, NULL, NULL),
(77, 11, 'мен очень нравиться даное завидение 👍👍\r\nя виталя масалов', '2025-11-28 13:18:53', NULL, 5, 0, 5, 1, 1, 12),
(78, 12, 'меня НЕ УСТРАИВАЕТ то что мне грубят в этом офисе!!! относятся как к скоту, беспредел!!😡😡😡', '2025-11-28 13:21:46', NULL, 6, 0, 3, 3, 2, 29),
(79, 8, 'у меня такая же ситуация, надо уволить всех их на ф*г😤', '2025-11-28 13:22:52', 78, 4, 0, NULL, NULL, NULL, NULL),
(80, 8, 'ты там даж не работаешь🤣🤣', '2025-11-28 13:24:47', 75, 3, 0, NULL, NULL, NULL, NULL),
(81, 6, 'это неправда забаньте хейтера', '2025-11-28 13:30:51', 75, 2, 0, NULL, NULL, NULL, NULL),
(82, 6, 'тут воняет бензином!!!!!!!!!!!!🤢🤢🤢🤢🤢🤢🤢🤢🤢🤢🤢🤢🤢', '2025-11-28 13:32:22', NULL, 0, 3, 4, 2, 2, 21),
(83, 3, 'ну ясен пень это же заправка', '2025-11-28 13:33:14', 82, 2, 0, NULL, NULL, NULL, NULL),
(84, 3, '🤣🤣', '2025-11-28 13:33:37', 77, 0, 1, NULL, NULL, NULL, NULL),
(85, 8, 'Привет!', '2025-11-28 13:33:53', 77, 1, 0, NULL, NULL, NULL, NULL),
(86, 7, '28 ноября 2025г. я приобрела в вашем магазине по адресу: Московский проспект, д. 212, сыр Гауда (акт продажи № 123). После вскрытия упаковки дома, я обнаружила внутри посторонний предмет (саморез), что является нарушением санитарных норм и правил производства пищевых продуктов.', '2025-11-28 13:38:20', NULL, 0, 2, 2, 1, 2, 9),
(87, 11, 'тётя это роснефть а не магазин продуктов ало', '2025-11-28 13:38:55', 86, 1, 0, NULL, NULL, NULL, NULL);

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
(274, 71, 1, 1),
(275, 12, 1, 1),
(277, 72, 8, 1),
(279, 71, 9, 1),
(280, 73, 9, 1),
(281, 72, 9, 1),
(282, 74, 9, 1),
(283, 75, 10, -1),
(284, 71, 10, 1),
(285, 12, 10, 1),
(286, 72, 10, 1),
(287, 73, 10, 1),
(288, 74, 10, 1),
(292, 75, 11, -1),
(293, 71, 11, 1),
(294, 12, 11, 1),
(296, 77, 11, 1),
(297, 12, 12, 1),
(298, 71, 12, 1),
(299, 77, 12, 1),
(300, 75, 12, -1),
(301, 76, 12, -1),
(302, 78, 12, 1),
(306, 78, 8, 1),
(307, 71, 8, 1),
(308, 76, 8, -1),
(310, 74, 8, 1),
(311, 73, 8, -1),
(312, 79, 8, 1),
(313, 80, 8, 1),
(318, 72, 4, 1),
(319, 73, 4, 1),
(320, 74, 4, 1),
(321, 77, 4, 1),
(322, 78, 4, 1),
(323, 79, 4, 1),
(324, 75, 4, -1),
(325, 80, 4, 1),
(327, 76, 4, -1),
(329, 12, 4, 1),
(330, 71, 4, 1),
(331, 12, 6, 1),
(332, 76, 6, -1),
(333, 71, 6, 1),
(334, 72, 6, 1),
(336, 73, 6, 1),
(337, 74, 6, 1),
(338, 77, 6, 1),
(339, 78, 6, 1),
(340, 79, 6, 1),
(341, 75, 6, -1),
(342, 81, 6, 1),
(343, 80, 6, 1),
(344, 82, 6, -1),
(346, 12, 3, 1),
(347, 76, 3, -1),
(348, 75, 3, -1),
(349, 81, 3, 1),
(350, 82, 3, -1),
(351, 83, 3, 1),
(352, 78, 3, 1),
(353, 79, 3, 1),
(354, 77, 8, 1),
(355, 85, 8, 1),
(357, 84, 8, -1),
(358, 12, 8, 1),
(359, 75, 8, -1),
(361, 86, 11, -1),
(362, 87, 11, 1),
(363, 12, 7, 1),
(364, 78, 7, 1),
(365, 75, 7, -1),
(366, 76, 7, -1),
(367, 86, 7, -1),
(368, 82, 7, -1),
(369, 83, 7, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `offices`
--

CREATE TABLE `offices` (
  `id` int NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
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
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
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
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role` enum('user','admin') COLLATE utf8mb4_general_ci DEFAULT 'user'
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
(7, 'gaga', '$2y$10$Hh.B8zAIYURaRWJU6cW1SuEb1dQEtbZP4hRad6k8AZ2GE5DtQtG62', '2025-11-21 23:28:58', 'user'),
(8, 'adler', '$2y$10$GQ2zr7qKcSds5o0fctkBj.a/8vUpFjUMEe8Gf/SlYBq.rzxc8AYci', '2025-11-28 13:03:45', 'user'),
(9, 'lukoil', '$2y$10$/36fn9dFHjlPb6iRqMk6IOyZvUSjUVXw2bSEx4WjrM1X4vvZLAvqK', '2025-11-28 13:09:08', 'user'),
(10, 'hater', '$2y$10$I0qFfVw8bb9X9DidnAu/vOn.NgiMIqVkbMvCB2MTGZo9Iv0bXMWw6', '2025-11-28 13:14:04', 'user'),
(11, 'vitala', '$2y$10$sXtALXerjLgC7K074ntTiu83uKnVTKTh/yNFD7DqXHWNQFCsoYj.i', '2025-11-28 13:16:23', 'user'),
(12, 'boss', '$2y$10$BtWzda7OpjrheS1NChiAIude8IOzMy1Wq3fCU7kayrI9g6H3Cmg42', '2025-11-28 13:19:57', 'user');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT для таблицы `message_votes`
--
ALTER TABLE `message_votes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=370;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
