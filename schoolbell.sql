-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 29, 2014 at 03:50 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `schoolbell`
--

--
-- Dumping data for table `alarm`
--

INSERT INTO `alarm` (`ID`, `bell_id`, `active`, `time`, `days`) VALUES
(2, 2, '1', '7:47', '1111100'),
(15, 5, '1', '9:40', '1111100'),
(16, 5, '1', '9:41', '1111000'),
(17, 5, '1', '9:44', '1111100');

--
-- Dumping data for table `bell`
--

INSERT INTO `bell` (`ID`, `plan_id`, `title`, `melody`) VALUES
(2, 1, 'Bubbles', 'bubbles.mp3'),
(5, 1, 'BigBen', 'hourlychimebeg.mp3');

--
-- Dumping data for table `plan`
--

INSERT INTO `plan` (`ID`, `active`, `title`) VALUES
(1, '1', 'Elso');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
