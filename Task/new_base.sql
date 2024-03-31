-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 31 2024 г., 22:02
-- Версия сервера: 8.0.24
-- Версия PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `new_base`
--

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `login` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `name`, `status`) VALUES
(4, 'kormushkina.mar@mail.ru', '$2y$10$Kd097z4FGujJoT7ZuYIFoOO1kp09qiFOBtU/k.W9nXJohF/T8YNHi', '', 'perfect'),
(5, 'kormushkina.ma@mail.ru', '$2y$10$rLDvncqwfFollPSJKAFwUOzqBx/iRtkd.N7oF8K07jXOPLTxfMjQC', '', 'perfect'),
(6, 'kormushkina.m@mail.ru', '$2y$10$cguZSHUWXuNiJlVU1GYkIeFmFOZy9wV702wwN7WY2M10YyVtv5Nke', '', 'perfect'),
(7, 'kormushkina.esges@mail.ru', '$2y$10$zmblMjJ37u2kGsSegp3JcuWGuzgsNfD5dc3rnIy2hDcV.ZBXGtn.2', '', 'perfect'),
(8, 'kormushkina.esge@mail.ru', '$2y$10$VolWdz8taAVqvig.UKBWPOTJXs1SLtuKc9MHI4gOhzvd2hurxmnQy', '', 'perfect'),
(9, 'kormushkina.ese@mail.ru', '$2y$10$IhkFcNdSlCQAcRTj65IiWOmeimPsDJN/HxJkkjxb1vJ2DTCKC9xny', '', 'perfect'),
(10, 'kormushkina.eser@mail.ru', '$2y$10$Nr0IY74envutQgONgihBPeopx43U4vut8JbiKpzsd2uu5Sc2r2iVO', '', 'perfect'),
(11, 'kormushkina.es@mail.ru', '$2y$10$UGRx8npsqmJGu94LyWW7AeUQDsI99.bT2rRJ8oz2POOSML8qFoLtC', '', 'perfect'),
(12, 'kormushkina.k@mail.ru', '$2y$10$0MumxLO8QxuPndX4AE0N4.gFGw4RDR3EVAhUk4Ty0Dn5Bz6qS21.2', '', 'perfect'),
(13, 'kormushkina.ty@mail.ru', '$2y$10$FVQ4Z.Gfdf.2N7YVw8nwl.rePxYMUr2vdCcYAANUtgTxa26EGwuI2', '', 'good'),
(14, 'awtesrfhgj@bgfbb.com', '$2y$10$MlH9bxvMi/pumO7icMTsg.q2shHLlGcn5AsBNgoADB9bs0Hh3KmOq', '', 'perfect'),
(15, 'kormushkina.tfg@mail.ru', '$2y$10$dyvT8Y6.wsTkRs7IXPjBree9cg.xY86k16iVtlnZa5yH6YIv868w6', '', 'perfect'),
(16, 'kormushkina.tyrt@mail.ru', '$2y$10$cGV9zvR6qNcxy0NqxDj6vee2wuGMOgIlI7NiCEGQeOLlCo1qoErE2', '', 'perfect'),
(17, 'kormushkina.t@mail.ru', '$2y$10$yW4wdj6MN8bQKQzqtKysR.NylnI0TAJQuPKgbl3xlXdh16PE4agW.', '', 'perfect'),
(18, 'kormushkina.tuuu@mail.ru', '$2y$10$RxfbyeH7DS.79ul9CeYzQ.inUIeY5agyBC66sQjwkSw2DTKG4OZnC', '', 'perfect'),
(19, 'kormushkina.tfssvgsrdbdbdbrgrgrs@mail.ru', '$2y$10$FSQJmDpfjQ3uihSWqDgPouZATbz5i3r6b8FlQqkt2WXfJh2HPBA3.', '', 'perfect'),
(20, 'kormushkina.katyaq@mail.ru', '$2y$10$ChiFxw1yawCWrKnwPm8KoOtTbEsc9q.u84wM1rcnNIoNmRRp.1/RK', '', 'perfect'),
(21, 'kormushkina.katyaqggg@mail.ru', '$2y$10$fByZCA2y1CFeWrnSScjUVuc42vt/jfdOePGlEP9ACfDMnPHAJmlYO', '', 'perfect');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
