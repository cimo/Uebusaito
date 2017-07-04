-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Lug 04, 2017 alle 17:28
-- Versione del server: 5.5.55-0ubuntu0.14.04.1
-- Versione PHP: 5.5.9-1ubuntu4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `uebusaito`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

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

CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'center',
  `sort` int(11) DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dump dei dati per la tabella `modules`
--

INSERT INTO `modules` (`id`, `position`, `sort`, `name`, `label`, `file_name`, `active`) VALUES
(1, 'header', 2, 'Menu root', 'module_1', 'menu_root.html.twig', 1),
(2, 'left', 0, 'Authentication', 'module_2', 'authentication.html.twig', 1),
(3, 'center', 0, 'Page', 'module_3', 'page_view.html.twig', 1),
(4, 'header', 0, 'Language', 'module_4', 'language_text.html.twig', 1),
(5, 'header', 1, 'Search', 'module_5', 'search.html.twig', 1),
(6, 'right', 0, 'Empty', 'module_6', 'empty.html.twig', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `only_link` tinyint(1) NOT NULL DEFAULT '1',
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dump dei dati per la tabella `pages`
--

INSERT INTO `pages` (`id`, `parent`, `controller_action`, `role_id`, `protected`, `show_in_menu`, `only_link`, `link`) VALUES
(1, NULL, 'UebusaitoBundle:ControlPanel:render', '1,2,', 1, 0, 0, '-'),
(2, NULL, NULL, '1,2,', 0, 1, 0, '-'),
(3, NULL, 'UebusaitoBundle:Registration:render', '1,2,', 0, 0, 0, '-'),
(4, NULL, 'UebusaitoBundle:RecoverPassword:render', '1,2,', 0, 0, 0, '-'),
(5, NULL, 'UebusaitoBundle:Search:render', '1,2,', 0, 0, 0, '-'),
(6, NULL, NULL, '4,', 1, 1, 0, '-'),
(7, NULL, NULL, '1,2,', 0, 1, 0, '-'),
(8, 7, NULL, '1,2,', 0, 1, 0, '-'),
(9, 8, NULL, '1,2,', 0, 1, 0, '-'),
(10, 8, NULL, '1,2,', 0, 1, 0, '-'),
(11, 9, NULL, '1,2,', 0, 1, 0, '-'),
(12, 7, NULL, '1,2,', 0, 1, 1, 'http://www.google.it'),
(13, 11, NULL, '1,2,', 0, 1, 0, '-');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_arguments`
--

CREATE TABLE IF NOT EXISTS `pages_arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` longtext COLLATE utf8_unicode_ci,
  `it` longtext COLLATE utf8_unicode_ci,
  `jp` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dump dei dati per la tabella `pages_arguments`
--

INSERT INTO `pages_arguments` (`id`, `en`, `it`, `jp`) VALUES
(1, 'Here you can administrate the website, choise the section on bottom menu.', 'Argomento pannello di controllo.', NULL),
(2, 'This is a cms created with symfony framework.', 'Argomento home.', 'ホームノアーギュメント。'),
(3, 'Registration argument.', 'Argomento registrazione.', NULL),
(4, 'Recover password argument.', 'Argomento recupero password.', NULL),
(5, 'Search argument.', 'Argomento cerca.', 'サーチノアーギュメント。'),
(6, 'Test argument.', 'Argomento test.', 'テストノアーギュメント。'),
(7, 'Test parent argument.', 'Argomento test genitore.', NULL),
(8, 'Test children 1 argument.', 'Argomento test figlio 1.', NULL),
(9, 'Test children 2 argument.', 'Argomento test figlio 2.', NULL),
(10, 'Test 2 argument.', 'Argomento test 2.', NULL),
(11, 'Test children 3 argument.', 'Argomento test figlio 3.', NULL),
(12, 'Test 1 argument.', 'Argomento test 1.', NULL),
(13, 'Test children 4 argument.', 'Argomento test figlio 4.', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_menu_names`
--

CREATE TABLE IF NOT EXISTS `pages_menu_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dump dei dati per la tabella `pages_menu_names`
--

INSERT INTO `pages_menu_names` (`id`, `en`, `it`, `jp`) VALUES
(1, '-', '-', '-'),
(2, 'Home', 'Home', 'ホーム'),
(3, '-', '-', '-'),
(4, '-', '-', '-'),
(5, '-', '-', '-'),
(6, 'Test', 'Test', 'テスト'),
(7, 'Test parent', 'Test genitore', NULL),
(8, 'Test children 1', 'Test figlio 1', NULL),
(9, 'Test children 2', 'Test figlio 2', NULL),
(10, 'Test 2', 'Test 2', NULL),
(11, 'Test children 3', 'Test figlio 3', NULL),
(12, 'Test 1', 'Test 1', NULL),
(13, 'Test children 4', 'Test figlio 4', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `pages_titles`
--

CREATE TABLE IF NOT EXISTS `pages_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dump dei dati per la tabella `pages_titles`
--

INSERT INTO `pages_titles` (`id`, `en`, `it`, `jp`) VALUES
(1, 'Control panel title', 'Titolo pannello di controllo', 'コントロールパネルノタイトル'),
(2, 'Home title', 'Titolo home', 'ホームノタイトル'),
(3, 'Registration title', 'Titolo registrazione', '登録ノタイトル'),
(4, 'Recover password title', 'Titolo recupero password', 'パスワードを回復ノタイトル'),
(5, 'Search title', 'Titolo cerca', 'サーチノタイトル'),
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

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `transaction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receiver` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `item_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

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

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_admin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '2,4',
  `https` tinyint(1) NOT NULL DEFAULT '1',
  `registration_user_confirm_admin` tinyint(1) NOT NULL DEFAULT '1',
  `login_attempt_time` int(11) NOT NULL DEFAULT '15',
  `login_attempt_count` int(11) NOT NULL DEFAULT '3',
  `captcha` tinyint(4) NOT NULL DEFAULT '0',
  `payPal_sandbox` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_business` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payPal_currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `payPal_credit_amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.01',
  `credits` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `email_admin`, `template`, `language`, `active`, `role_id`, `https`, `registration_user_confirm_admin`, `login_attempt_time`, `login_attempt_count`, `captcha`, `payPal_sandbox`, `payPal_business`, `payPal_currency_code`, `payPal_credit_amount`, `credits`) VALUES
(1, 'user_1@reinventsoftware.org', 'basic', 'en', 1, '2,3,', 1, 0, 15, 3, 0, 1, 'paypal.business@gmail.com', 'USD', '0.01', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `attempt_login` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `name`, `surname`, `email`, `telephone`, `born`, `gender`, `fiscal_code`, `company_name`, `vat`, `website`, `state`, `city`, `zip`, `address`, `password`, `credits`, `not_locked`, `date_registration`, `date_current_login`, `date_last_login`, `help_code`, `ip`, `attempt_login`) VALUES
(1, '1,2,', 'user_1', 'cimo', 'dago', 'user_1@reinventsoftware.org', '3491234567', '1984-04-11', 'm', NULL, NULL, NULL, 'http://www.reinventsoftware.org', 'Italia', 'Roma', '00100', 'Via', '$2y$13$3xYFbs9D8AphLEXmLHUuiOJI1G.kF/nEfbU7J7wsJuANmKNUa2Pvi', 0, 1, '2016-08-04 10:25:12', '2017-07-04 17:20:24', '2017-07-04 13:00:27', NULL, '95.247.79.253', 0),
(2, '1,4,', 'test_1', NULL, NULL, 'test_1@reinventsoftware.org', NULL, '1960-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2', 11, 1, '2016-09-10 17:39:31', '2017-07-03 18:12:48', '2017-07-03 18:11:33', NULL, '95.247.79.253', 0),
(3, '1,', 'test_2', NULL, NULL, 'test_2@reinventsoftware.org', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq', 0, 0, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `users_roles`
--

CREATE TABLE IF NOT EXISTS `users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dump dei dati per la tabella `users_roles`
--

INSERT INTO `users_roles` (`id`, `level`) VALUES
(1, 'ROLE_USER'),
(2, 'ROLE_ADMIN'),
(3, 'ROLE_MODERATOR'),
(4, 'ROLE_TEST');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;