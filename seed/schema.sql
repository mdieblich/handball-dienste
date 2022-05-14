CREATE TABLE `dienstedienst`.`mannschaft` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `name` VARCHAR(256) NOT NULL , 
    `liga` VARCHAR(256) NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `dienstedienst`.`person` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `name` VARCHAR(512) NOT NULL , 
    `email` VARCHAR(512) NOT NULL , 
    `hauptmannschaft` INT NULL , 
    PRIMARY KEY (`id`),
    FOREIGN KEY (`hauptmannschaft`) REFERENCES `mannschaft`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `dienstedienst`.`gegner` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `name` VARCHAR(256) NOT NULL ,
    `liga` VARCHAR(256) NULL , 
    `stelltSekretaerBeiHeimspiel` TINYINT NOT NULL DEFAULT '0' , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `dienstedienst`.`spiel` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `nuliga_id` VARCHAR(256) NOT NULL , 
    `mannschaft` INT NOT NULL , 
    `gegner` INT NOT NULL , 
    `heimspiel` TINYINT NOT NULL DEFAULT '0' , 
    `halle` int NOT NULL , 
    `anwurf` DATETIME NOT NULL , 
    PRIMARY KEY (`id`), 
    INDEX `index_anwurf` (`anwurf`),
    FOREIGN KEY (`mannschaft`) REFERENCES `mannschaft`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`gegner`) REFERENCES `gegner`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE `dienstedienst`.`dienst` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `spiel` INT NOT NULL , 
    `dienstart` VARCHAR(256) NOT NULL , 
    `mannschaft` INT NOT NULL , 
    `person` INT NULL , 
    PRIMARY KEY (`id`),
    FOREIGN KEY (`spiel`) REFERENCES `spiel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`mannschaft`) REFERENCES `mannschaft`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`person`) REFERENCES `person`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;