CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id` VARCHAR(128) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `timestamp` INT(10) UNSIGNED DEFAULT 0 NOT NULL,
    `data` BLOB NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
);

CREATE TABLE IF NOT EXISTS `uploads` (
  `id` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(20) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `createdAt` DATETIME NOT NULL DEFAULT now(),
    `editedAt` DATETIME NULL ON UPDATE now(),
    `deletedAt` DATETIME NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `authorId` INT NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `content` TEXT(4000) NOT NULL,
    `createdAt` DATETIME NOT NULL DEFAULT now(),
    `autoArchiveAt` DATETIME NULL,
    `editedAt` DATETIME NULL ON UPDATE now(),
    `deletedAt` DATETIME NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (authorId) REFERENCES users(id)
);


CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `postId` INT NOT NULL,
    `authorId` INT NOT NULL,
    `content` TEXT(4000) NOT NULL,
    `createdAt` DATETIME NOT NULL DEFAULT now(),
    `editedAt` DATETIME NULL ON UPDATE now(),
    `deletedAt` DATETIME NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (authorId) REFERENCES users(id),
    FOREIGN KEY (postId) REFERENCES posts(id)
);


CREATE TABLE IF NOT EXISTS `views` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `postId` INT NOT NULL,
    `viewerId` INT NULL,
    `viewedAt` DATETIME NOT NULL DEFAULT now(),
    PRIMARY KEY (id),
    FOREIGN KEY (postId) REFERENCES posts(id),
    FOREIGN KEY (viewerId) REFERENCES users(id)
);


CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS `post_tags` (
    `tagId` INT NOT NULL,
    `postId` INT NOT NULL,
    FOREIGN KEY (postId) REFERENCES posts(id),
    FOREIGN KEY (tagId) REFERENCES tags(id)
);

INSERT INTO `tags` (`name`, `description`) VALUES 
('Question', 'Tag for questions about stocks or investment'),
('Discussion', 'For discussing certains stocks'),
('Strategy', 'Discussing the strategies to use for investment in stocks'),
('Hot', 'Stocks that have had a lot of new recent investers'),
('High-Risk', 'Stocks that have high volatility'),
('Low-Risk', 'Stocks that have low volatility'),
('To the moon', 'Stocks that might have high return in the future'),
('High Returns', 'Stocks that have high returns at the moment'),
('Risk-Free', 'Stocks that are known to have a positive return albiet can be low'),
('BTC', NULL),
('ETH', NULL),
('AAPL', NULL),
('AMZN', NULL);
