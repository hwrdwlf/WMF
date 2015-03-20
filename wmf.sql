-- phpMyAdmin SQL Dump
-- version 4.2.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 20, 2015 at 05:45 AM
-- Server version: 5.5.38
-- PHP Version: 5.6.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `wmf`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_currency`
--

CREATE TABLE `daily_currency` (
`id` int(11) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `rate` varchar(10) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `daily_currency`
--

INSERT INTO `daily_currency` (`id`, `currency`, `rate`) VALUES
(1, 'JPY', '0.013125'),
(2, 'BGN', '0.6707'),
(3, 'CZK', '0.05190'),
(4, 'ARS', '0.2294'),
(5, 'AUD', '1.0689'),
(6, 'CHF', '1.1154');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_currency`
--
ALTER TABLE `daily_currency`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `currency` (`currency`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_currency`
--
ALTER TABLE `daily_currency`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=122;