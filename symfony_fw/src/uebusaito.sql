-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Lug 22, 2018 alle 08:55
-- Versione del server: 10.1.34-MariaDB
-- Versione PHP: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uebusaito`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y-m-d'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `languages`
--

INSERT INTO `languages` (`id`, `code`, `date`) VALUES
(1, 'en', 'Y-m-d'),
(2, 'jp', 'Y-m-d'),
(3, 'it', 'd-m-Y');

-- --------------------------------------------------------

--
-- Struttura della tabella `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'center',
  `position_tmp` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rank_in_column` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `controller_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `modules`
--

INSERT INTO `modules` (`id`, `position`, `position_tmp`, `rank_in_column`, `name`, `label`, `controller_name`, `active`) VALUES
(1, 'header', NULL, 1, 'Search', 'module_1', 'SearchController::moduleAction', 1),
(2, 'left', NULL, 1, 'Authentication', 'module_2', 'AuthenticationController::moduleAction', 1),
(3, 'center', NULL, 1, 'Page', 'module_3', 'PageViewController::moduleAction', 1),
(4, 'right', NULL, 1, 'Empty', 'module_4', 'EmptyController::moduleAction', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `parent` int(11) DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `position_in_menu` int(11) DEFAULT NULL,
  `comment` tinyint(1) NOT NULL DEFAULT '1',
  `only_link` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `user_creation` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `date_creation` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_modification` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `date_modification` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages`
--

INSERT INTO `pages` (`id`, `alias`, `parent`, `controller_action`, `role_user_id`, `protected`, `show_in_menu`, `position_in_menu`, `comment`, `only_link`, `link`, `user_creation`, `date_creation`, `user_modification`, `date_modification`) VALUES
(1, 'controlPanel', NULL, 'App\\Controller\\ControlPanelController::renderAction', '1,2,', 1, 0, 1, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(2, 'home', NULL, NULL, '1,2,', 0, 1, 2, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(3, 'registration', NULL, 'App\\Controller\\RegistrationController::renderAction', '1,2,', 0, 0, 3, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(4, 'recover_password', NULL, 'App\\Controller\\RecoverPasswordController::renderAction', '1,2,', 0, 0, 4, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(5, 'search', NULL, 'App\\Controller\\SearchController::renderAction', '1,2,', 0, 0, 5, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(6, 'test', NULL, 'App\\Controller\\PageControllerAction\\IncludeTestController::renderAction', '4,', 1, 1, 7, 1, 0, '-', '-', '0000-00-00 00:00:00', 'cimo', '2017-11-11 12:27:27'),
(7, 'test_parent', NULL, NULL, '1,2,', 0, 1, 6, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(8, 'test_children_1', 7, NULL, '1,2,', 0, 1, 1, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(9, 'test_children_2', 8, NULL, '1,2,', 0, 1, 1, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(10, 'test_2', 8, NULL, '1,2,', 0, 1, 3, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(11, 'test_children_3', 9, NULL, '1,2,', 0, 1, 1, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(12, 'test_1', 7, NULL, '1,2,', 0, 1, 2, 1, 1, 'http://www.google.it', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00'),
(13, 'test_children_4', 11, NULL, '1,2,', 0, 1, 1, 1, 0, '-', '-', '0000-00-00 00:00:00', '-', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_arguments`
--

CREATE TABLE `pages_arguments` (
  `id` int(11) NOT NULL,
  `en` longtext COLLATE utf8_unicode_ci,
  `it` longtext COLLATE utf8_unicode_ci,
  `jp` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages_arguments`
--

INSERT INTO `pages_arguments` (`id`, `en`, `it`, `jp`) VALUES
(1, 'Here you can administrate the website, choise the section on bottom menu.', 'Argomento pannello di controllo.', 'コントロールパネルのアーギュメント。'),
(2, 'This is a cms created with symfony framework.', 'Argomento home.', 'ホームのアーギュメント。'),
(3, 'Registration argument.', 'Argomento registrazione.', '登録のアーギュメント。'),
(4, 'Recover password argument.', 'Argomento recupero password.', 'パスワードを回復のアーギュメント。'),
(5, 'Search argument.', 'Argomento cerca.', 'サーチのアーギュメント。'),
(6, 'Test argument.', 'Argomento test.', NULL),
(7, 'Test parent argument.', 'Argomento test genitore.', NULL),
(8, 'Test children 1 argument.', 'Argomento test figlio 1.', NULL),
(9, 'Test children 2 argument.', 'Argomento test figlio 2.', NULL),
(10, 'Test 2 argument.', 'Argomento test 2.', NULL),
(11, 'Test children 3 argument.', 'Argomento test figlio 3.', NULL),
(12, 'Test 1 argument.', 'Argomento test 1.', NULL),
(13, 'Test children 4 argument.', 'Argomento test figlio 4.', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_comments`
--

CREATE TABLE `pages_comments` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `username_reply` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `argument` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_creation` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modification` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages_comments`
--

INSERT INTO `pages_comments` (`id`, `page_id`, `username`, `username_reply`, `argument`, `date_creation`, `date_modification`) VALUES
(1, 6, 'cimo', NULL, 'Comment test.', '2017-10-31 11:45:22', '0000-00-00 00:00:00'),
(2, 6, 'test_1', NULL, 'New comment test.', '2017-10-31 11:55:18', '2017-11-01 23:51:35'),
(3, 6, 'cimo', 'test_1', 'Test over!', '2017-11-02 11:05:28', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_menu_names`
--

CREATE TABLE `pages_menu_names` (
  `id` int(11) NOT NULL,
  `en` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `it` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `jp` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages_menu_names`
--

INSERT INTO `pages_menu_names` (`id`, `en`, `it`, `jp`) VALUES
(1, '-', '-', '-'),
(2, 'Home', 'Home', '-'),
(3, '-', '-', '-'),
(4, '-', '-', '-'),
(5, '-', '-', '-'),
(6, 'Test', 'Test', '-'),
(7, 'Test parent', 'Test genitore', '-'),
(8, 'Test children 1', 'Test figlio 1', '-'),
(9, 'Test children 2', 'Test figlio 2', '-'),
(10, 'Test 2', 'Test 2', '-'),
(11, 'Test children 3', 'Test figlio 3', '-'),
(12, 'Test 1', 'Test 1', '-'),
(13, 'Test children 4', 'Test figlio 4', '-');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_titles`
--

CREATE TABLE `pages_titles` (
  `id` int(11) NOT NULL,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages_titles`
--

INSERT INTO `pages_titles` (`id`, `en`, `it`, `jp`) VALUES
(1, 'Control panel title', 'Titolo pannello di controllo', 'コントロールパネルのタイトル'),
(2, 'Home title', 'Titolo home', 'ホームのタイトル'),
(3, 'Registration title', 'Titolo registrazione', '登録のタイトル'),
(4, 'Recover password title', 'Titolo recupero password', 'パスワードを回復のタイトル'),
(5, 'Search title', 'Titolo cerca', 'サーチのタイトル'),
(6, 'Test title', 'Titolo test', 'テストのタイトル'),
(7, 'Test parent title', 'Titolo test genitore', NULL),
(8, 'Test children 1 title', 'Titolo test figlio 1', NULL),
(9, 'Test children 2 title', 'Titolo test figlio 2', NULL),
(10, 'Test 2 title', 'Titolo test 2', NULL),
(11, 'Test children 3 title', 'Titolo test figlio 3', NULL),
(12, 'Test 1 title', 'Titolo test 1', NULL),
(13, 'Test children 4 title', 'Titolo test figlio 4', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receiver` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `item_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `transaction`, `date`, `status`, `payer`, `receiver`, `currency_code`, `item_name`, `amount`, `quantity`) VALUES
(1, 2, '419530066K1260733', '09:19:50 Jul 04, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.01', '1'),
(2, 2, '1KY910617X114632S', '05:16:37 Jul 07, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.01', '1'),
(3, 2, '19U375700V802405E', '05:31:11 Jul 07, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.01', '1'),
(4, 2, '7JV630061G061150L', '06:10:56 Jul 07, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.01', '1'),
(5, 2, '87V61799HN941194T', '06:15:57 Jul 07, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.05', '5'),
(6, 2, '6FU073223W956013S', '06:26:31 Jul 07, 2016 PDT', 'Completed', 'WGRYMAE6MYEP4', '5CLM69V9C3PVW', 'USD', 'credits', '0.02', '2');

-- --------------------------------------------------------

--
-- Struttura della tabella `roles_users`
--

CREATE TABLE `roles_users` (
  `id` int(11) NOT NULL,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `roles_users`
--

INSERT INTO `roles_users` (`id`, `level`) VALUES
(1, 'ROLE_USER'),
(2, 'ROLE_ADMIN'),
(3, 'ROLE_MODERATOR'),
(4, 'ROLE_TEST');

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `template_column` int(1) NOT NULL DEFAULT '1',
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `email_admin` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `website_active` tinyint(1) NOT NULL DEFAULT '1',
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '2,3',
  `https` tinyint(1) NOT NULL DEFAULT '1',
  `registration_user_confirm_admin` tinyint(1) NOT NULL DEFAULT '1',
  `login_attempt_time` int(11) NOT NULL DEFAULT '15',
  `login_attempt_count` int(11) NOT NULL DEFAULT '3',
  `captcha` tinyint(1) NOT NULL DEFAULT '0',
  `page_date` tinyint(1) NOT NULL DEFAULT '1',
  `page_comment` tinyint(1) NOT NULL DEFAULT '1',
  `page_comment_active` tinyint(1) NOT NULL DEFAULT '1',
  `payPal_sandbox` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_business` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payPal_currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `payPal_credit_amount` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.01',
  `credit` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `template`, `template_column`, `language`, `email_admin`, `website_active`, `role_user_id`, `https`, `registration_user_confirm_admin`, `login_attempt_time`, `login_attempt_count`, `captcha`, `page_date`, `page_comment`, `page_comment_active`, `payPal_sandbox`, `payPal_business`, `payPal_currency_code`, `payPal_credit_amount`, `credit`) VALUES
(1, 'basic', 1, 'en', 'cimo@reinventsoftware.org', 1, '2,3,', 0, 0, 15, 3, 0, 1, 1, 1, 1, 'paypal.business@gmail.com', 'USD', '0.01', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,',
  `roles` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'ROLE_USER,',
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telephone` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `born` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00',
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `credit` int(11) NOT NULL DEFAULT '0',
  `not_locked` tinyint(1) NOT NULL DEFAULT '0',
  `date_registration` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_current_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_last_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `help_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attempt_login` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `role_user_id`, `roles`, `username`, `name`, `surname`, `email`, `telephone`, `born`, `gender`, `fiscal_code`, `company_name`, `vat`, `website`, `state`, `city`, `zip`, `address`, `password`, `credit`, `not_locked`, `date_registration`, `date_current_login`, `date_last_login`, `help_code`, `ip`, `attempt_login`) VALUES
(1, '1,2,', 'ROLE_USER,ROLE_ADMIN', 'cimo', 'Simone', 'D\'Agostino', 'cimo@reinventsoftware.org', '3491234567', '1984-4-11', 'm', NULL, NULL, NULL, 'https://www.reinventsoftware.org', 'Japan', 'Tokyo', '100-0001', 'Street', '$2y$13$dwkh0OFE.Jz2PxvlxUvjIO4kQM92elYrRTDB4VEy1LGALx0bOuVj6', 0, 1, '2016-08-04 10:25:12', '2018-07-22 07:38:57', '2018-07-22 07:03:08', NULL, '127.0.0.1', 0),
(2, '1,3,', 'ROLE_USER,ROLE_MODERATOR', 'test_1', NULL, NULL, 'test_1@reinventsoftware.org', NULL, '1960-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2', 11, 1, '2016-09-10 17:39:31', '2017-12-03 13:13:27', '2017-11-06 21:48:31', NULL, '79.51.52.39', 0),
(3, '1,4,', 'ROLE_USER,ROLE_TEST', 'test_2', NULL, NULL, 'test_2@reinventsoftware.org', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq', 0, 0, '0000-00-00 00:00:00', '2017-10-14 14:31:11', '0000-00-00 00:00:00', NULL, '87.11.116.214', 0);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pages_arguments`
--
ALTER TABLE `pages_arguments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indici per le tabelle `pages_comments`
--
ALTER TABLE `pages_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pages_menu_names`
--
ALTER TABLE `pages_menu_names`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pages_titles`
--
ALTER TABLE `pages_titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indici per le tabelle `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `roles_users`
--
ALTER TABLE `roles_users`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `pages_arguments`
--
ALTER TABLE `pages_arguments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `pages_comments`
--
ALTER TABLE `pages_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `pages_menu_names`
--
ALTER TABLE `pages_menu_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `pages_titles`
--
ALTER TABLE `pages_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `roles_users`
--
ALTER TABLE `roles_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
