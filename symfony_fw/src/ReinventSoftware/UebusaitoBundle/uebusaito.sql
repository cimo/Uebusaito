-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: uebusaito
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'en'),(2,'it'),(3,'jp');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'center',
  `position_tmp` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position_in_column` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'header',NULL,3,'Menu root','module_1','menu_root.html.twig',1),(2,'left',NULL,1,'Authentication','module_2','authentication.html.twig',1),(3,'center',NULL,1,'Page','module_3','page_view.html.twig',1),(4,'header',NULL,1,'Language','module_4','language_text.html.twig',1),(5,'header',NULL,2,'Search','module_5','search.html.twig',1),(6,'right',NULL,1,'Empty','module_6','empty.html.twig',1);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `position_in_menu` int(11) DEFAULT NULL,
  `only_link` tinyint(1) NOT NULL DEFAULT '1',
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'controlPanel',NULL,'UebusaitoBundle:ControlPanel:render','1,2,',1,0,1,0,'-'),(2,'home',NULL,NULL,'1,2,',0,1,2,0,'-'),(3,'registration',NULL,'UebusaitoBundle:Registration:render','1,2,',0,0,3,0,'-'),(4,'recover_password',NULL,'UebusaitoBundle:RecoverPassword:render','1,2,',0,0,4,0,'-'),(5,'search',NULL,'UebusaitoBundle:Search:render','1,2,',0,0,5,0,'-'),(6,'test',NULL,'UebusaitoBundle:PageControllerAction\\IncludeTest:render','4,',1,1,7,0,'-'),(7,'test_parent',NULL,NULL,'1,2,',0,1,6,0,'-'),(8,'test_children_1',7,NULL,'1,2,',0,1,1,0,'-'),(9,'test_children_2',8,NULL,'1,2,',0,1,1,0,'-'),(10,'test_2',8,NULL,'1,2,',0,1,2,0,'-'),(11,'test_children_3',9,NULL,'1,2,',0,1,1,0,'-'),(12,'test_1',7,NULL,'1,2,',0,1,2,1,'http://www.google.it'),(13,'test_children_4',11,NULL,'1,2,',0,1,1,0,'-');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_arguments`
--

DROP TABLE IF EXISTS `pages_arguments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` longtext COLLATE utf8_unicode_ci,
  `it` longtext COLLATE utf8_unicode_ci,
  `jp` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_arguments`
--

LOCK TABLES `pages_arguments` WRITE;
/*!40000 ALTER TABLE `pages_arguments` DISABLE KEYS */;
INSERT INTO `pages_arguments` VALUES (1,'&lt;p&gt;Here you can administrate the website, choise the section on bottom menu.&lt;/p&gt;','&lt;p&gt;Argomento pannello di controllo.&lt;/p&gt;','&lt;p&gt;コントロールパネルのアーギュメント。&lt;/p&gt;'),(2,'&lt;p&gt;This is a cms created with symfony framework.&lt;/p&gt;','&lt;p&gt;Argomento home.&lt;/p&gt;','&lt;p&gt;ホームのアーギュメント。&lt;/p&gt;'),(3,'&lt;p&gt;Registration argument.&lt;/p&gt;','&lt;p&gt;Argomento registrazione.&lt;/p&gt;','&lt;p&gt;登録のアーギュメント。&lt;/p&gt;'),(4,'&lt;p&gt;Recover password argument.&lt;/p&gt;','&lt;p&gt;Argomento recupero password.&lt;/p&gt;','&lt;p&gt;パスワードを回復のアーギュメント。&lt;/p&gt;'),(5,'&lt;p&gt;Search argument.&lt;/p&gt;','&lt;p&gt;Argomento cerca.&lt;/p&gt;','&lt;p&gt;サーチのアーギュメント。&lt;/p&gt;'),(6,'&lt;p&gt;Test argument.&lt;/p&gt;','&lt;p&gt;Argomento test.&lt;/p&gt;',NULL),(7,'&lt;p&gt;Test parent argument.&lt;/p&gt;','&lt;p&gt;Argomento test genitore.&lt;/p&gt;',NULL),(8,'&lt;p&gt;Test children 1 argument.&lt;/p&gt;','&lt;p&gt;Argomento test figlio 1.&lt;/p&gt;',NULL),(9,'&lt;p&gt;Test children 2 argument.&lt;/p&gt;','&lt;p&gt;Argomento test figlio 2.&lt;/p&gt;',NULL),(10,'&lt;p&gt;Test 2 argument.&lt;/p&gt;','&lt;p&gt;Argomento test 2.&lt;/p&gt;',NULL),(11,'&lt;p&gt;Test children 3 argument.&lt;/p&gt;','&lt;p&gt;Argomento test figlio 3.&lt;/p&gt;',NULL),(12,'&lt;p&gt;Test 1 argument.&lt;/p&gt;','&lt;p&gt;Argomento test 1.&lt;/p&gt;',NULL),(13,'&lt;p&gt;Test children 4 argument.&lt;/p&gt;','&lt;p&gt;Argomento test figlio 4.&lt;/p&gt;',NULL);
/*!40000 ALTER TABLE `pages_arguments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_menu_names`
--

DROP TABLE IF EXISTS `pages_menu_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_menu_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `it` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `jp` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_menu_names`
--

LOCK TABLES `pages_menu_names` WRITE;
/*!40000 ALTER TABLE `pages_menu_names` DISABLE KEYS */;
INSERT INTO `pages_menu_names` VALUES (1,'-','-','-'),(2,'Home','Home','-'),(3,'-','-','-'),(4,'-','-','-'),(5,'-','-','-'),(6,'Test','Test','-'),(7,'Test parent','Test genitore','-'),(8,'Test children 1','Test figlio 1','-'),(9,'Test children 2','Test figlio 2','-'),(10,'Test 2','Test 2','-'),(11,'Test children 3','Test figlio 3','-'),(12,'Test 1','Test 1','-'),(13,'Test children 4','Test figlio 4','-');
/*!40000 ALTER TABLE `pages_menu_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_titles`
--

DROP TABLE IF EXISTS `pages_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_titles`
--

LOCK TABLES `pages_titles` WRITE;
/*!40000 ALTER TABLE `pages_titles` DISABLE KEYS */;
INSERT INTO `pages_titles` VALUES (1,'Control panel title','Titolo pannello di controllo','コントロールパネルのタイトル'),(2,'Home title','Titolo home','ホームのタイトル'),(3,'Registration title','Titolo registrazione','登録のタイトル'),(4,'Recover password title','Titolo recupero password','パスワードを回復のタイトル'),(5,'Search title','Titolo cerca','サーチのタイトル'),(6,'Test title','Titolo test','テストのタイトル'),(7,'Test parent title','Titolo test genitore',NULL),(8,'Test children 1 title','Titolo test figlio 1',NULL),(9,'Test children 2 title','Titolo test figlio 2',NULL),(10,'Test 2 title','Titolo test 2',NULL),(11,'Test children 3 title','Titolo test figlio 3',NULL),(12,'Test 1 title','Titolo test 1',NULL),(13,'Test children 4 title','Titolo test figlio 4',NULL);
/*!40000 ALTER TABLE `pages_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,2,'419530066K1260733','09:19:50 Jul 04, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1'),(2,2,'1KY910617X114632S','05:16:37 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1'),(3,2,'19U375700V802405E','05:31:11 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1'),(4,2,'7JV630061G061150L','06:10:56 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1'),(5,2,'87V61799HN941194T','06:15:57 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.05','5'),(6,2,'6FU073223W956013S','06:26:31 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.02','2');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `template_column` int(1) NOT NULL DEFAULT '1',
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `email_admin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '2,3',
  `https` tinyint(1) NOT NULL DEFAULT '1',
  `registration_user_confirm_admin` tinyint(1) NOT NULL DEFAULT '1',
  `login_attempt_time` int(11) NOT NULL DEFAULT '15',
  `login_attempt_count` int(11) NOT NULL DEFAULT '3',
  `captcha` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_sandbox` tinyint(1) NOT NULL DEFAULT '0',
  `payPal_business` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payPal_currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
  `payPal_credit_amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.01',
  `credits` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'basic',1,'en','cimo@reinventsoftware.org',1,'2,3,',1,0,15,3,0,1,'paypal.business@gmail.com','USD','0.01',1);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,',
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `telephone` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `born` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00',
  `gender` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `date_registration` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_current_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_last_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `help_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attempt_login` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'1,2,','cimo','cimo','dago','cimo@reinventsoftware.org','3491234567','1984-04-11','m',NULL,NULL,NULL,'https://www.reinventsoftware.org','Italia','Roma','00100','Via','$2y$13$3xYFbs9D8AphLEXmLHUuiOJI1G.kF/nEfbU7J7wsJuANmKNUa2Pvi',0,1,'2016-08-04 10:25:12','2017-10-29 16:14:15','2017-10-28 11:35:58',NULL,'79.13.12.142',0),(2,'1,4,','test_1',NULL,NULL,'test_1@reinventsoftware.org',NULL,'1960-12-30',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2',0,1,'2016-09-10 17:39:31','2017-10-14 14:46:56','2017-10-14 14:46:24',NULL,'87.11.116.214',0),(3,'1,','test_2',NULL,NULL,'test_2@reinventsoftware.org',NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq',0,1,'0000-00-00 00:00:00','2017-10-14 14:31:11','0000-00-00 00:00:00',NULL,'87.11.116.214',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,'ROLE_USER'),(2,'ROLE_ADMIN'),(3,'ROLE_MODERATOR'),(4,'ROLE_TEST');
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'uebusaito'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-10-29 16:19:51
