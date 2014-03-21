-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 27, 2014 at 08:48 PM
-- Server version: 5.5.32
-- PHP Version: 5.3.10-1ubuntu3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergy_list`
--

CREATE TABLE IF NOT EXISTS `allergy_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;

--
-- Dumping data for table `allergy_list`
--

INSERT INTO `allergy_list` (`id`, `label`) VALUES
(1, 'milk'),
(2, 'lactose'),
(3, 'eggs'),
(4, 'peanuts'),
(5, 'soy'),
(6, 'tree nuts'),
(7, 'wheat'),
(8, 'gluten'),
(9, 'fish'),
(10, 'shellfish'),
(11, 'coconut'),
(12, 'strawberries'),
(13, 'cinnamon'),
(14, 'carrots'),
(15, 'red food coloring'),
(16, 'citrus');


--
-- Table structure for table `attendance`
--

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `note` longtext,
  `service` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4405 ;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_notification_queue`
--

CREATE TABLE IF NOT EXISTS `attendance_notification_queue` (
  `id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `msg` text,
  `time` int(11) DEFAULT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `child2allergy`
--

CREATE TABLE IF NOT EXISTS `child2allergy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` int(11) NOT NULL,
  `allergy_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `child_id` (`child_id`),
  KEY `allergy_id` (`allergy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=640 ;

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE IF NOT EXISTS `children` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` text,
  `last_name` text,
  `birthday` int(11) DEFAULT NULL,
  `parent_note` longtext,
  `status` text,
  `last_room` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=379 ;

-- --------------------------------------------------------

--
-- Table structure for table `children_old`
--

CREATE TABLE IF NOT EXISTS `children_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` text,
  `last_name` text,
  `birthday` int(11) DEFAULT NULL,
  `allergies` longtext,
  `status` text,
  `last_room` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=202 ;

-- --------------------------------------------------------

--
-- Table structure for table `household2child`
--

CREATE TABLE IF NOT EXISTS `household2child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `household_id` int(11) DEFAULT NULL,
  `child_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=376 ;

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE IF NOT EXISTS `households` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `household_name` text,
  `home_phone` text,
  `email` text,
  `cell_phone` text,
  `address` text,
  `city` text,
  `state` text,
  `zip` text,
  `date` int(11) DEFAULT NULL,
  `civicrm_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=256 ;

-- --------------------------------------------------------

--
-- Table structure for table `imported_households`
--

CREATE TABLE IF NOT EXISTS `imported_households` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `household_name` text,
  `home_phone` text,
  `email` text,
  `cell_phone` text,
  `address` text,
  `city` text,
  `state` text,
  `zip` text,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `household_id` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `note` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
