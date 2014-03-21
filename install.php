<?php

$doing_install = True;
include "lib.php";
global $db;

$schema = <<<HEREDOC
	SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
	SET time_zone = "+00:00";
	/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
	/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
	/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
	/*!40101 SET NAMES utf8 */;

	DROP TABLE IF EXISTS `allergy_list`;
	
	CREATE TABLE IF NOT EXISTS `allergy_list` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `label` text NOT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ;

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
	DROP TABLE IF EXISTS `attendance`;
	
	CREATE TABLE IF NOT EXISTS `attendance` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `child_id` int(11) DEFAULT NULL,
	  `date` int(11) DEFAULT NULL,
	  `room_id` int(11) DEFAULT NULL,
	  `note` longtext,
	  `service` tinytext,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ;

	-- --------------------------------------------------------

	--
	-- Table structure for table `attendance_notification_queue`
	--
	
	DROP TABLE IF EXISTS `attendance_notification_queue`;
	CREATE TABLE IF NOT EXISTS `attendance_notification_queue` (
	  `id` int(11) NOT NULL,
	  `child_id` int(11) DEFAULT NULL,
	  `msg` text,
	  `time` int(11) DEFAULT NULL,
	  `attendance_id` int(11) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8;

	-- --------------------------------------------------------

	--
	-- Table structure for table `child2allergy`
	--
	
	DROP TABLE IF EXISTS `child2allergy`;
	CREATE TABLE IF NOT EXISTS `child2allergy` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `child_id` int(11) NOT NULL,
	  `allergy_id` int(11) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `child_id` (`child_id`),
	  KEY `allergy_id` (`allergy_id`)
	) DEFAULT CHARSET=utf8 ;

	-- --------------------------------------------------------

	--
	-- Table structure for table `children`
	--
	DROP TABLE IF EXISTS `children`;
	CREATE TABLE IF NOT EXISTS `children` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `first_name` text,
	  `last_name` text,
	  `birthday` int(11) DEFAULT NULL,
	  `parent_note` longtext,
	  `status` text,
	  `last_room` int(11) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ;

	-- --------------------------------------------------------

	--
	-- Table structure for table `household2child`
	--
	
	DROP TABLE IF EXISTS `household2child`;
	CREATE TABLE IF NOT EXISTS `household2child` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `household_id` int(11) DEFAULT NULL,
	  `child_id` int(11) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ;

	-- --------------------------------------------------------

	--
	-- Table structure for table `households`
	--
	DROP TABLE IF EXISTS `households`;
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
	) DEFAULT CHARSET=utf8 ;

	-- --------------------------------------------------------

	--
	-- Table structure for table `notes`
	--
	DROP TABLE IF EXISTS `notes`;
	CREATE TABLE IF NOT EXISTS `notes` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `household_id` int(11) DEFAULT NULL,
	  `date` int(11) DEFAULT NULL,
	  `note` longtext,
	  PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8 ;

	/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
	/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
	/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
HEREDOC;

$stage = 1;
if (isset($_GET['stage'])) $stage = $_GET['stage'];

?>


<html>
<head>
	<style>
	body {font-size:24pt;margin:20px auto; width:60%;background-color:black;font-family:sans-serif;}
	div {background-color:#eee;border:1px solid #eee; border-radius:20px;text-align:center;padding:5%;}
	.button {display:inline-block; border: 1px solid black; padding:10px; border-radius:12px; background:#aaa;color:black;}
	.button:hover {background:white;}
	a {text-decoration:none;}
	</style>
</head>
<body>
	<?php if ($stage == 1): ?>
		<div>
			This script will delete and reinstall the kid registration database. Are you sure you want to continue?
			<br />
			<a class="button" href="install.php?stage=2">Yes</a>
		</div>
	<?php elseif ($stage == 2): ?>
	<div>
		<?php
			echo "<p>Running the installation script</p>";
			$db->multi_query($schema);
		?>
		
		If there were no error messages, then the database tables were successfully installed.
		Click <a class="button" href="index.php">HERE</a> to go to the main site.
	</div>
	<?php endif; ?>
</body>
</html>
