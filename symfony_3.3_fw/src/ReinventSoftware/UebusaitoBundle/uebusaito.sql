-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Ago 15, 2017 alle 23:17
-- Versione del server: 5.7.19-0ubuntu0.16.04.1
-- Versione PHP: 7.0.18-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `languages`
--

INSERT INTO `languages` (`id`, `code`) VALUES
(1, 'en'),
(2, 'it'),
(3, 'jp');

-- --------------------------------------------------------

--
-- Struttura della tabella `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'center',
  `position_tmp` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sort` int(11) DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `modules`
--

INSERT INTO `modules` (`id`, `position`, `position_tmp`, `sort`, `name`, `label`, `file_name`, `active`) VALUES
(1, 'header', '', 2, 'Menu root', 'module_1', 'menu_root.html.twig', 1),
(2, 'left', '', 0, 'Authentication', 'module_2', 'authentication.html.twig', 1),
(3, 'center', '', 0, 'Page', 'module_3', 'page_view.html.twig', 1),
(4, 'header', '', 0, 'Language', 'module_4', 'language_text.html.twig', 1),
(5, 'header', '', 1, 'Search', 'module_5', 'search.html.twig', 1),
(6, 'right', '', 0, 'Empty', 'module_6', 'empty.html.twig', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `only_link` tinyint(1) NOT NULL DEFAULT '1',
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `pages`
--

INSERT INTO `pages` (`id`, `alias`, `parent`, `controller_action`, `role_id`, `protected`, `show_in_menu`, `only_link`, `link`) VALUES
(1, 'control_panel', NULL, 'UebusaitoBundle:ControlPanel:render', '1,2,', 1, 0, 0, '-'),
(2, 'home', NULL, NULL, '1,2,', 0, 1, 0, '-'),
(3, 'registration', NULL, 'UebusaitoBundle:Registration:render', '1,2,', 0, 0, 0, '-'),
(4, 'recover_password', NULL, 'UebusaitoBundle:RecoverPassword:render', '1,2,', 0, 0, 0, '-'),
(5, 'search', NULL, 'UebusaitoBundle:Search:render', '1,2,', 0, 0, 0, '-'),
(6, 'test', NULL, NULL, '4,', 1, 1, 0, '-'),
(7, 'test_parent', NULL, NULL, '1,2,', 0, 1, 0, '-'),
(8, 'test_children_1', 7, NULL, '1,2,', 0, 1, 0, '-'),
(9, 'test_children_2', 8, NULL, '1,2,', 0, 1, 0, '-'),
(10, 'test_2', 8, NULL, '1,2,', 0, 1, 0, '-'),
(11, 'test_children_3', 9, NULL, '1,2,', 0, 1, 0, '-'),
(12, 'test_1', 7, NULL, '1,2,', 0, 1, 1, 'http://www.google.it'),
(13, 'test_children_4', 11, NULL, '1,2,', 0, 1, 0, '-');

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
(1, '&lt;p&gt;Here you can administrate the website, choise the section on bottom menu.&lt;/p&gt;', '&lt;p&gt;Argomento pannello di controllo.&lt;/p&gt;', '&lt;p&gt;コントロールパネルのアーギュメント。&lt;/p&gt;'),
(2, '&lt;p&gt;This is a cms created with symfony framework.&lt;/p&gt;', '&lt;p&gt;Argomento home.&lt;/p&gt;', '&lt;p&gt;ホームのアーギュメント。&lt;/p&gt;'),
(3, '&lt;p&gt;Registration argument.&lt;/p&gt;', '&lt;p&gt;Argomento registrazione.&lt;/p&gt;', '&lt;p&gt;登録のアーギュメント。&lt;/p&gt;'),
(4, '&lt;p&gt;Recover password argument.&lt;/p&gt;', '&lt;p&gt;Argomento recupero password.&lt;/p&gt;', '&lt;p&gt;パスワードを回復のアーギュメント。&lt;/p&gt;'),
(5, '&lt;p&gt;Search argument.&lt;/p&gt;', '&lt;p&gt;Argomento cerca.&lt;/p&gt;', '&lt;p&gt;サーチのアーギュメント。&lt;/p&gt;'),
(6, '&lt;p&gt;Test argument.&lt;/p&gt;', '&lt;p&gt;Argomento test.&lt;/p&gt;', NULL),
(7, '&lt;p&gt;Test parent argument.&lt;/p&gt;', '&lt;p&gt;Argomento test genitore.&lt;/p&gt;', NULL),
(8, '&lt;p&gt;Test children 1 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test figlio 1.&lt;/p&gt;', NULL),
(9, '&lt;p&gt;Test children 2 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test figlio 2.&lt;/p&gt;', NULL),
(10, '&lt;p&gt;Test 2 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test 2.&lt;/p&gt;', NULL),
(11, '&lt;p&gt;Test children 3 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test figlio 3.&lt;/p&gt;', NULL),
(12, '&lt;p&gt;Test 1 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test 1.&lt;/p&gt;', NULL),
(13, '&lt;p&gt;Test children 4 argument.&lt;/p&gt;', '&lt;p&gt;Argomento test figlio 4.&lt;/p&gt;', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_menu_names`
--

CREATE TABLE `pages_menu_names` (
  `id` int(11) NOT NULL,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT '-',
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT '-',
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT '-'
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
(6, NULL, NULL, NULL),
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
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `email_admin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `template_column` int(1) NOT NULL DEFAULT '1',
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '2,4',
  `https` tinyint(1) NOT NULL DEFAULT '1',
  `registration_user_confirm_admin` tinyint(1) NOT NULL DEFAULT '1',
  `login_attempt_time` int(11) NOT NULL DEFAULT '15',
  `login_attempt_count` int(11) NOT NULL DEFAULT '3',
  `captcha` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_sandbox` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_business` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payPal_currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `payPal_credit_amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.01',
  `credits` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `email_admin`, `template`, `template_column`, `language`, `active`, `role_id`, `https`, `registration_user_confirm_admin`, `login_attempt_time`, `login_attempt_count`, `captcha`, `payPal_sandbox`, `payPal_business`, `payPal_currency_code`, `payPal_credit_amount`, `credits`) VALUES
(1, 'cimo@reinventsoftware.org', 'basic', 1, 'en', 1, '2,3,', 1, 0, 15, 3, 0, 1, 'paypal.business@gmail.com', 'USD', '0.01', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `born` varchar(10) COLLATE utf8_unicode_ci DEFAULT '0000-00-00',
  `gender` varchar(6) COLLATE utf8_unicode_ci DEFAULT 'male',
  `fiscal_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `credits` int(11) NOT NULL DEFAULT '0',
  `not_locked` tinyint(1) NOT NULL DEFAULT '0',
  `date_registration` varchar(19) COLLATE utf8_unicode_ci DEFAULT '0000-00-00 00:00:00',
  `date_current_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_last_login` varchar(19) COLLATE utf8_unicode_ci DEFAULT '0000-00-00 00:00:00',
  `help_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attempt_login` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `name`, `surname`, `email`, `telephone`, `born`, `gender`, `fiscal_code`, `company_name`, `vat`, `website`, `state`, `city`, `zip`, `address`, `password`, `credits`, `not_locked`, `date_registration`, `date_current_login`, `date_last_login`, `help_code`, `ip`, `attempt_login`) VALUES
(1, '1,2,', 'cimo', 'cimo', 'dago', 'cimo@reinventsoftware.org', '3491234567', '1984-04-11', 'm', NULL, NULL, NULL, 'https://www.reinventsoftware.org', 'Italia', 'Roma', '00100', 'Via', '$2y$13$3xYFbs9D8AphLEXmLHUuiOJI1G.kF/nEfbU7J7wsJuANmKNUa2Pvi', 0, 1, '2016-08-04 10:25:12', '2017-08-15 23:13:03', '2017-08-15 23:12:04', NULL, '79.35.223.203', 0),
(2, '1,4,', 'test_1', NULL, NULL, 'test_1@reinventsoftware.org', NULL, '1960-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2', 11, 1, '2016-09-10 17:39:31', '2017-07-03 18:12:48', '2017-07-03 18:11:33', NULL, '95.247.79.253', 0),
(3, '1,', 'test_2', NULL, NULL, 'test_2@reinventsoftware.org', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq', 0, 0, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `users_roles`
--

CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `users_roles`
--

INSERT INTO `users_roles` (`id`, `level`) VALUES
(1, 'ROLE_USER'),
(2, 'ROLE_ADMIN'),
(3, 'ROLE_MODERATOR'),
(4, 'ROLE_TEST');

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
-- Indici per le tabelle `users_roles`
--
ALTER TABLE `users_roles`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
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
-- AUTO_INCREMENT per la tabella `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT per la tabella `users_roles`
--
ALTER TABLE `users_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
