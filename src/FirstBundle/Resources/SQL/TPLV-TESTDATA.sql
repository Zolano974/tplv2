-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 06, 2016 at 02:19 PM
-- Server version: 10.1.18-MariaDB
-- PHP Version: 7.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

--
-- Dumping data for table `field`
--

INSERT INTO `field` (`id`, `workset_id`, `name`, `color`, `order_custom`) VALUES
(1, 1, 'Zobologie', '#9f07c5', NULL),
(2, 1, 'Neurologie', '#89a0eb', NULL),
(3, 1, 'Conniologie', '#d9e9b4', NULL);

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`id`, `field_id`, `name`, `number`) VALUES
(1, 3, 'Connio', 100),
(2, 3, 'conniasse', 101),
(3, 3, 'Conionnono', 105),
(4, 1, 'zobby', 245),
(5, 1, 'zobou', 265),
(6, 1, 'zobBOB', 213),
(7, 2, 'Neuropathie', 310),
(8, 2, 'Crises Nervolosies', 312),
(9, 1, 'Folie Pure', 313);

--
-- Dumping data for table `item_mikbook`
--

INSERT INTO `item_mikbook` (`id`, `item_id`, `user_id`) VALUES
(3, 4, 1),
(4, 5, 1),
(5, 7, 1),
(6, 8, 1),
(7, 2, 1);

--
-- Dumping data for table `kanban_item_step`
--

INSERT INTO `kanban_item_step` (`id`, `item_id`, `iteration`, `user_id`, `step`) VALUES
(1, 4, 1, 1, 0),
(2, 5, 1, 1, 0),
(3, 6, 1, 1, 0),
(4, 9, 1, 1, 0),
(5, 7, 1, 1, 0),
(6, 8, 1, 1, 0),
(7, 1, 1, 1, 0),
(8, 2, 1, 1, 0),
(9, 3, 1, 1, 0),
(10, 4, 2, 1, 0),
(11, 5, 2, 1, 0),
(12, 6, 2, 1, 0),
(13, 9, 2, 1, 0),
(14, 7, 2, 1, 0),
(15, 8, 2, 1, 0),
(16, 1, 2, 1, 0),
(17, 2, 2, 1, 0),
(18, 3, 2, 1, 0),
(19, 4, 3, 1, 0),
(20, 5, 3, 1, 0),
(21, 6, 3, 1, 0),
(22, 9, 3, 1, 0),
(23, 7, 3, 1, 0),
(24, 8, 3, 1, 0),
(25, 1, 3, 1, 0),
(26, 2, 3, 1, 0),
(27, 3, 3, 1, 0);

--
-- Dumping data for table `link_tour_field`
--

INSERT INTO `link_tour_field` (`id`, `tour_id`, `field_id`, `user_id`, `done`) VALUES
(1, 1, 1, 1, 1),
(2, 1, 2, 1, 1),
(3, 1, 3, 1, 0),
(4, 2, 1, 1, 0),
(5, 2, 2, 1, 0),
(6, 2, 3, 1, 0),
(7, 3, 1, 1, 0),
(8, 3, 2, 1, 0),
(9, 3, 3, 1, 0);

--
-- Dumping data for table `link_tour_item`
--

INSERT INTO `link_tour_item` (`id`, `tour_id`, `item_id`, `user_id`, `done`) VALUES
(1, 1, 4, 1, 1),
(2, 1, 5, 1, 1),
(3, 1, 6, 1, 1),
(4, 1, 9, 1, 1),
(5, 1, 7, 1, 1),
(6, 1, 8, 1, 1),
(7, 1, 1, 1, 1),
(8, 1, 2, 1, 0),
(9, 1, 3, 1, 0),
(10, 2, 4, 1, 0),
(11, 2, 5, 1, 0),
(12, 2, 6, 1, 0),
(13, 2, 9, 1, 0),
(14, 2, 7, 1, 0),
(15, 2, 8, 1, 0),
(16, 2, 1, 1, 0),
(17, 2, 2, 1, 0),
(18, 2, 3, 1, 0),
(19, 3, 4, 1, 0),
(20, 3, 5, 1, 0),
(21, 3, 6, 1, 0),
(22, 3, 9, 1, 0),
(23, 3, 7, 1, 0),
(24, 3, 8, 1, 0),
(25, 3, 1, 1, 0),
(26, 3, 2, 1, 0),
(27, 3, 3, 1, 0);

--
-- Dumping data for table `tour`
--

INSERT INTO `tour` (`id`, `user_id`, `iteration`, `workset_id`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1);

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `surname`, `login`, `password`) VALUES
(1, 'Sophie', 'MouAmoureuse', 'sophie', 'wxcvbn');

--
-- Dumping data for table `workset`
--

INSERT INTO `workset` (`id`, `name`, `description`, `generic`) VALUES
(1, 'Médecine', 'Révision du programme de Médecine pour l\'ECN (portant sur D2, D3 & D4)', 1),
(2, 'ZOB', 'ZOB', 0);

-- --------------------------------------------------------

--
-- Structure for view `kanbanXitem`
--
DROP TABLE IF EXISTS `kanbanXitem`;

CREATE ALGORITHM=UNDEFINED DEFINER=`zolano`@`localhost` SQL SECURITY DEFINER VIEW `kanbanXitem`  AS  select `t`.`item_id` AS `item_id`,`i`.`name` AS `item_name`,`t`.`iteration` AS `iteration`,`t`.`field_id` AS `field_id`,`t`.`user_id` AS `user_id`,`k`.`step` AS `step`,`ti`.`done` AS `done` from (((`tourXitem` `t` left join `kanban_item_step` `k` on(((`k`.`item_id` = `t`.`item_id`) and (`k`.`user_id` = `t`.`user_id`) and (`k`.`iteration` = `t`.`iteration`)))) left join `item` `i` on((`t`.`item_id` = `i`.`id`))) left join `link_tour_item` `ti` on(((`t`.`tour_id` = `ti`.`tour_id`) and (`t`.`user_id` = `ti`.`user_id`) and (`t`.`item_id` = `ti`.`item_id`)))) ;

-- --------------------------------------------------------

--
-- Structure for view `tourXitem`
--
DROP TABLE IF EXISTS `tourXitem`;

CREATE ALGORITHM=UNDEFINED DEFINER=`zolano`@`localhost` SQL SECURITY DEFINER VIEW `tourXitem`  AS  select `t`.`id` AS `tour_id`,`t`.`iteration` AS `iteration`,`t`.`workset_id` AS `workset_id`,`li`.`item_id` AS `item_id`,`i`.`field_id` AS `field_id`,`li`.`done` AS `done`,`u`.`id` AS `user_id`,`u`.`login` AS `login` from ((`item` `i` left join (`tour` `t` left join `link_tour_item` `li` on((`li`.`tour_id` = `t`.`id`))) on((`li`.`item_id` = `i`.`id`))) left join `user` `u` on((`u`.`id` = `t`.`user_id`))) order by `u`.`id`,`t`.`iteration` ;

-- --------------------------------------------------------

--
-- Structure for view `view_link_user_field`
--
DROP TABLE IF EXISTS `view_link_user_field`;

CREATE ALGORITHM=UNDEFINED DEFINER=`zolano`@`localhost` SQL SECURITY DEFINER VIEW `view_link_user_field`  AS  select `t`.`id` AS `tour_id`,`t`.`iteration` AS `iteration`,`u`.`id` AS `user_id`,`u`.`login` AS `user`,`lf`.`field_id` AS `field_id`,`lf`.`done` AS `done` from ((`tour` `t` left join `link_tour_field` `lf` on((`lf`.`tour_id` = `t`.`id`))) left join `user` `u` on((`t`.`user_id` = `u`.`id`))) ;

-- --------------------------------------------------------

--
-- Structure for view `view_link_user_item`
--
DROP TABLE IF EXISTS `view_link_user_item`;

CREATE ALGORITHM=UNDEFINED DEFINER=`zolano`@`localhost` SQL SECURITY DEFINER VIEW `view_link_user_item`  AS  select `t`.`id` AS `tour_id`,`t`.`iteration` AS `iteration`,`u`.`id` AS `user_id`,`u`.`login` AS `user`,`li`.`item_id` AS `item_id`,`li`.`done` AS `done` from ((`tour` `t` left join `link_tour_item` `li` on((`li`.`tour_id` = `t`.`id`))) left join `user` `u` on((`t`.`user_id` = `u`.`id`))) ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
