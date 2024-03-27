CREATE DATABASE IF NOT EXISTS `xinstitutedb` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `xinstitutedb`;

-- Table structure for table `students`
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50),
    `last_name` VARCHAR(50),
    `birthday` DATE,
    `address` VARCHAR(255),
    `contact_number` VARCHAR(15),
    `course_name` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `courses`
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_name` VARCHAR(100),
    `department` VARCHAR(50),
    `fee` DECIMAL(10,2)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` varchar(50) NOT NULL,
    `password` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;