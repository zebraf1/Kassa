
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- fos_user
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `fos_user`;

CREATE TABLE `fos_user`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255),
    `username_canonical` VARCHAR(255),
    `email` VARCHAR(255),
    `email_canonical` VARCHAR(255),
    `enabled` TINYINT(1) DEFAULT 0,
    `salt` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `last_login` DATETIME,
    `locked` TINYINT(1) DEFAULT 0,
    `expired` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME,
    `confirmation_token` VARCHAR(255),
    `password_requested_at` DATETIME,
    `credentials_expired` TINYINT(1) DEFAULT 0,
    `credentials_expire_at` DATETIME,
    `roles` TEXT,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `fos_user_U_1` (`username_canonical`),
    UNIQUE INDEX `fos_user_U_2` (`email_canonical`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- fos_group
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `fos_group`;

CREATE TABLE `fos_group`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `roles` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- fos_user_group
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `fos_user_group`;

CREATE TABLE `fos_user_group`
(
    `fos_user_id` INTEGER NOT NULL,
    `fos_group_id` INTEGER NOT NULL,
    PRIMARY KEY (`fos_user_id`,`fos_group_id`),
    INDEX `fos_user_group_FI_2` (`fos_group_id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- ollekassa_product
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `ollekassa_product`;

CREATE TABLE `ollekassa_product`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `product_code` VARCHAR(100),
    `price` DECIMAL(10,2) NOT NULL,
    `amount_type_id` VARCHAR(50) DEFAULT 'PIECE',
    `status_id` VARCHAR(50) DEFAULT 'ACTIVE',
    `seq` INTEGER(3) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- ollekassa_report
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `ollekassa_report`;

CREATE TABLE `ollekassa_report`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `member_id` INTEGER,
    `cash` DECIMAL(10,2) NOT NULL,
    `type` VARCHAR(255) DEFAULT 'VERIFICATION' NOT NULL,
    `created_at` DATETIME,
    `updated_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `member_id` (`member_id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- ollekassa_report_row
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `ollekassa_report_row`;

CREATE TABLE `ollekassa_report_row`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `report_id` INTEGER,
    `product_id` INTEGER,
    `amount` DECIMAL(10,1) DEFAULT 0 NOT NULL,
    `current_price` DECIMAL(10,2),
    PRIMARY KEY (`id`),
    INDEX `FI_ort_row_product_fk` (`product_id`),
    INDEX `FI_ort_row_report_fk` (`report_id`),
    CONSTRAINT `report_row_product_fk`
        FOREIGN KEY (`product_id`)
        REFERENCES `ollekassa_product` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `report_row_report_fk`
        FOREIGN KEY (`report_id`)
        REFERENCES `ollekassa_report` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- ollekassa_transaction
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `ollekassa_transaction`;

CREATE TABLE `ollekassa_transaction`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `type` VARCHAR(255) NOT NULL,
    `product_id` INTEGER,
    `member_id` INTEGER NOT NULL,
    `amount` DECIMAL(10,1),
    `current_price` DECIMAL(10,2),
    `sum` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `member_id` (`member_id`),
    INDEX `FI_duct_purchase_product_fk` (`product_id`),
    INDEX `FI_duct_purchase_created_by_fk` (`created_by`),
    CONSTRAINT `product_purchase_product_fk`
        FOREIGN KEY (`product_id`)
        REFERENCES `ollekassa_product` (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

-- ---------------------------------------------------------------------
-- liikmed
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `liikmed`;

CREATE TABLE `liikmed`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `koondised_id` INTEGER DEFAULT -1 NOT NULL,
    `koondised_alg` INTEGER DEFAULT -1 NOT NULL,
    `staatused_id` INTEGER DEFAULT -1 NOT NULL,
    `eesnimi` VARCHAR(50),
    `perenimi` VARCHAR(50),
    `teaduskond` VARCHAR(50),
    `isa_eesnimi` VARCHAR(50),
    `isa_perenimi` VARCHAR(50),
    `mobiil` VARCHAR(50),
    `tootel` VARCHAR(50),
    `fax` VARCHAR(50),
    `email` VARCHAR(100),
    `msn` VARCHAR(100),
    `skype` VARCHAR(100),
    `syn_p` CHAR(2),
    `syn_k` CHAR(2),
    `syn_a` VARCHAR(4),
    `coet_p` CHAR(2),
    `coet_k` CHAR(2),
    `coet_a` VARCHAR(4),
    `coet_s` CHAR(2),
    `confr_p` CHAR(2),
    `confr_k` CHAR(2),
    `confr_a` VARCHAR(4),
    `confr_s` CHAR(2),
    `vil_p` CHAR(2),
    `vil_k` CHAR(2),
    `vil_a` VARCHAR(4),
    `vil_s` CHAR(2),
    `lahk_pohjused_id` INTEGER DEFAULT 0 NOT NULL,
    `lahk_p` CHAR(2),
    `lahk_k` CHAR(2),
    `lahk_a` VARCHAR(4),
    `lahk_s` CHAR(2),
    `eemal` INTEGER(1) NOT NULL,
    `markus` VARCHAR(200),
    `teated` INTEGER(1) DEFAULT 0 NOT NULL,
    `muutmise_aeg` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `muutja_id` INTEGER,
    `kustutatud` INTEGER(1) DEFAULT 0 NOT NULL,
    `aadress1` VARCHAR(100),
    `linn1` VARCHAR(100),
    `osariik1` VARCHAR(30),
    `indeks1` VARCHAR(20),
    `riik1` VARCHAR(100),
    `telefon1` VARCHAR(50),
    `aadress2` VARCHAR(100),
    `linn2` VARCHAR(100),
    `osariik2` VARCHAR(30),
    `indeks2` VARCHAR(20),
    `riik2` VARCHAR(100),
    `telefon2` VARCHAR(50),
    `tookoht` VARCHAR(100),
    `amet` VARCHAR(100),
    `leibcantus` VARCHAR(200),
    `tegevusala` VARCHAR(200) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `koondised_id` (`koondised_id`),
    INDEX `eesnimi` (`eesnimi`),
    INDEX `perenimi` (`perenimi`),
    INDEX `staatused_id` (`staatused_id`),
    INDEX `coet_a` (`coet_a`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- sessions
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions`
(
    `session` VARCHAR(40) DEFAULT '0' NOT NULL,
    `lastaccess` BIGINT,
    `usr_id` INTEGER,
    PRIMARY KEY (`session`),
    INDEX `usr_id` (`usr_id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(20) DEFAULT '' NOT NULL,
    `password` VARCHAR(20) DEFAULT '' NOT NULL,
    `liikmed_id` INTEGER DEFAULT 0 NOT NULL,
    `lastlogin` DATETIME NOT NULL,
    `jutukas_lastaccess` INTEGER DEFAULT 0 NOT NULL,
    `jutukas_firstmess` INTEGER,
    PRIMARY KEY (`id`),
    INDEX `username` (`username`),
    INDEX `liikmed_id` (`liikmed_id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- valved
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `valved`;

CREATE TABLE `valved`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `valve_tsyklid_id` INTEGER DEFAULT 0 NOT NULL,
    `kuupaev` DATE DEFAULT '0000-00-00' NOT NULL,
    `aeg` TINYINT DEFAULT 0 NOT NULL,
    `liikmed_id` INTEGER DEFAULT 0 NOT NULL,
    `majavan` TINYINT DEFAULT 0 NOT NULL,
    `puudus` INTEGER(1) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `FI_kmed_fk` (`liikmed_id`),
    INDEX `FI_ve_tsyklid_fk` (`valve_tsyklid_id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- valve_tsyklid
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `valve_tsyklid`;

CREATE TABLE `valve_tsyklid`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `nimi` VARCHAR(100) DEFAULT 'Valve' NOT NULL,
    `valvajad` enum('koik','reb','tl') DEFAULT 'koik' NOT NULL,
    `koondised_id` INTEGER DEFAULT 0 NOT NULL,
    `algaeg` DATE DEFAULT '0000-00-00' NOT NULL,
    `loppaeg` DATE DEFAULT '0000-00-00' NOT NULL,
    `etapp` TINYINT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `algaeg` (`algaeg`),
    INDEX `loppaeg` (`loppaeg`),
    INDEX `etapp` (`etapp`),
    INDEX `koondised_id` (`koondised_id`)
) ENGINE=MyISAM;

-- ---------------------------------------------------------------------
-- ollekassa_member_credit
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `ollekassa_member_credit`;

CREATE TABLE `ollekassa_member_credit`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `member_id` INTEGER NOT NULL,
    `credit` DECIMAL(10,2) DEFAULT 0 NOT NULL,
    `comment` VARCHAR(225) DEFAULT '',
    PRIMARY KEY (`id`),
    INDEX `member_id` (`member_id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
