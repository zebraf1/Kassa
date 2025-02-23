-- Database dump 23.02.2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET FOREIGN_KEY_CHECKS = 0;

# DROP DATABASE `kassa`;
# CREATE DATABASE IF NOT EXISTS `kassa` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_estonian_ci;
# USE `kassa`;

# DROP DATABASE IF EXISTS `kassa_test`;
CREATE DATABASE `kassa_test` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_estonian_ci;
USE `kassa_test`;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `koondised`
--

DROP TABLE IF EXISTS `koondised`;
CREATE TABLE `koondised` (
                             `id` int(11) NOT NULL,
                             `nimi` varchar(100) NOT NULL DEFAULT '',
                             `kassa_aktiivne` int(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `liikmed`
--

DROP TABLE IF EXISTS `liikmed`;
CREATE TABLE `liikmed` (
                           `id` int(11) NOT NULL,
                           `koondised_id` int(11) NOT NULL DEFAULT -1,
                           `koondised_alg` int(11) NOT NULL DEFAULT -1,
                           `staatused_id` int(11) NOT NULL DEFAULT -1,
                           `eesnimi` varchar(50) DEFAULT NULL,
                           `perenimi` varchar(50) DEFAULT NULL,
                           `isikukood` bigint(12) DEFAULT NULL,
                           `teaduskond` varchar(50) DEFAULT NULL,
                           `isa_eesnimi` varchar(50) DEFAULT NULL,
                           `isa_perenimi` varchar(50) DEFAULT NULL,
                           `mobiil` varchar(50) DEFAULT NULL,
                           `tootel` varchar(50) DEFAULT NULL,
                           `fax` varchar(50) DEFAULT NULL,
                           `email` varchar(100) DEFAULT NULL,
                           `msn` varchar(100) DEFAULT NULL,
                           `skype` varchar(100) DEFAULT NULL,
                           `syn_p` char(2) DEFAULT NULL,
                           `syn_k` char(2) DEFAULT NULL,
                           `syn_a` varchar(4) DEFAULT NULL,
                           `coet_p` char(2) DEFAULT NULL,
                           `coet_k` char(2) DEFAULT NULL,
                           `coet_a` varchar(4) DEFAULT NULL,
                           `coet_s` char(2) DEFAULT NULL,
                           `kuldrebane` int(1) NOT NULL DEFAULT 0,
                           `confr_p` char(2) DEFAULT NULL,
                           `confr_k` char(2) DEFAULT NULL,
                           `confr_a` varchar(4) DEFAULT NULL,
                           `confr_s` char(2) DEFAULT NULL,
                           `vil_p` char(2) DEFAULT NULL,
                           `vil_k` char(2) DEFAULT NULL,
                           `vil_a` varchar(4) DEFAULT NULL,
                           `vil_s` char(2) DEFAULT NULL,
                           `lahk_pohjused_id` int(11) NOT NULL DEFAULT 0,
                           `lahk_p` char(2) DEFAULT NULL,
                           `lahk_k` char(2) DEFAULT NULL,
                           `lahk_a` varchar(4) DEFAULT NULL,
                           `lahk_s` char(2) DEFAULT NULL,
                           `eemal` int(1) NOT NULL,
                           `markus` varchar(200) DEFAULT NULL,
                           `teated` int(1) NOT NULL DEFAULT 0,
                           `muutmise_aeg` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                           `muutja_id` int(11) DEFAULT NULL,
                           `kustutatud` int(1) NOT NULL DEFAULT 0,
                           `aadress1` varchar(100) DEFAULT NULL,
                           `linn1` varchar(100) DEFAULT NULL,
                           `osariik1` varchar(30) DEFAULT NULL,
                           `indeks1` varchar(20) DEFAULT NULL,
                           `riik1` varchar(100) DEFAULT NULL,
                           `telefon1` varchar(50) DEFAULT NULL,
                           `aadress2` varchar(100) DEFAULT NULL,
                           `linn2` varchar(100) DEFAULT NULL,
                           `osariik2` varchar(30) DEFAULT NULL,
                           `indeks2` varchar(20) DEFAULT NULL,
                           `riik2` varchar(100) DEFAULT NULL,
                           `telefon2` varchar(50) DEFAULT NULL,
                           `tookoht` varchar(100) DEFAULT NULL,
                           `amet` varchar(100) DEFAULT NULL,
                           `leibcantus` varchar(200) DEFAULT NULL,
                           `tegevusala` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_credit_netting`
--

DROP TABLE IF EXISTS `ollekassa_credit_netting`;
CREATE TABLE `ollekassa_credit_netting` (
                                            `id` int(11) NOT NULL,
                                            `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_credit_netting_row`
--

DROP TABLE IF EXISTS `ollekassa_credit_netting_row`;
CREATE TABLE `ollekassa_credit_netting_row` (
                                                `id` int(11) NOT NULL,
                                                `credit_netting_id` int(11) NOT NULL,
                                                `convent_id` int(11) NOT NULL,
                                                `sum` decimal(10,2) NOT NULL,
                                                `netting_done` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_member_credit`
--

DROP TABLE IF EXISTS `ollekassa_member_credit`;
CREATE TABLE `ollekassa_member_credit` (
                                           `id` int(11) NOT NULL,
                                           `member_id` int(11) NOT NULL,
                                           `convent_id` int(11) NOT NULL,
                                           `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
                                           `comment` varchar(225) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_point_of_sale`
--

DROP TABLE IF EXISTS `ollekassa_point_of_sale`;
CREATE TABLE `ollekassa_point_of_sale` (
                                           `id` int(11) NOT NULL,
                                           `name` varchar(100) NOT NULL,
                                           `convent_id` int(11) NOT NULL DEFAULT 6,
                                           `hash` varchar(100) NOT NULL,
                                           `device_info` varchar(255) DEFAULT NULL,
                                           `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                           `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_product`
--

DROP TABLE IF EXISTS `ollekassa_product`;
CREATE TABLE `ollekassa_product` (
                                     `id` int(11) NOT NULL,
                                     `name` varchar(100) NOT NULL,
                                     `product_code` varchar(100) DEFAULT NULL,
                                     `price` decimal(10,2) NOT NULL,
                                     `amount_type` varchar(50) DEFAULT 'PIECE',
                                     `amount` decimal(10,2) DEFAULT 1.00,
                                     `status` varchar(50) DEFAULT 'DISABLED',
                                     `seq` int(3) DEFAULT 1,
                                     `product_group_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_product_group`
--

DROP TABLE IF EXISTS `ollekassa_product_group`;
CREATE TABLE `ollekassa_product_group` (
                                           `id` int(11) NOT NULL,
                                           `name` varchar(100) NOT NULL,
                                           `seq` int(3) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_product_info`
--

DROP TABLE IF EXISTS `ollekassa_product_info`;
CREATE TABLE `ollekassa_product_info` (
                                          `id` int(11) NOT NULL,
                                          `product_id` int(11) NOT NULL,
                                          `convent_id` int(11) NOT NULL,
                                          `price` decimal(10,2) NOT NULL,
                                          `warehouse_count` decimal(10,2) DEFAULT 0.00,
                                          `storage_count` decimal(10,2) DEFAULT 0.00,
                                          `status` varchar(50) DEFAULT 'DISABLED',
                                          `resource_type` varchar(50) DEFAULT 'LIMITED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_report`
--

DROP TABLE IF EXISTS `ollekassa_report`;
CREATE TABLE `ollekassa_report` (
                                    `id` int(11) NOT NULL,
                                    `name` varchar(100) NOT NULL,
                                    `member_id` int(11) DEFAULT NULL,
                                    `convent_id` int(11) NOT NULL DEFAULT 6,
                                    `cash` decimal(10,2) NOT NULL,
                                    `type` varchar(255) NOT NULL DEFAULT 'VERIFICATION',
                                    `source` varchar(100) DEFAULT NULL,
                                    `target` varchar(100) DEFAULT NULL,
                                    `created_at` datetime DEFAULT NULL,
                                    `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_report_row`
--

DROP TABLE IF EXISTS `ollekassa_report_row`;
CREATE TABLE `ollekassa_report_row` (
                                        `id` int(11) NOT NULL,
                                        `report_id` int(11) DEFAULT NULL,
                                        `product_id` int(11) DEFAULT NULL,
                                        `count` decimal(10,1) NOT NULL DEFAULT 0.0,
                                        `current_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_setting`
--

DROP TABLE IF EXISTS `ollekassa_setting`;
CREATE TABLE `ollekassa_setting` (
                                     `id` int(11) NOT NULL,
                                     `object` varchar(20) NOT NULL,
                                     `object_id` int(11) NOT NULL,
                                     `reference` varchar(100) NOT NULL,
                                     `value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_staatused_credit_limit`
--

DROP TABLE IF EXISTS `ollekassa_staatused_credit_limit`;
CREATE TABLE `ollekassa_staatused_credit_limit` (
                                                    `id` int(11) NOT NULL,
                                                    `status_id` int(11) NOT NULL,
                                                    `credit_limit` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_transaction`
--

DROP TABLE IF EXISTS `ollekassa_transaction`;
CREATE TABLE `ollekassa_transaction` (
                                         `id` int(11) NOT NULL,
                                         `type` varchar(255) NOT NULL,
                                         `product_id` int(11) DEFAULT NULL,
                                         `member_id` int(11) DEFAULT NULL,
                                         `convent_id` int(11) NOT NULL,
                                         `pos_id` int(11) DEFAULT NULL,
                                         `count` decimal(10,1) DEFAULT NULL,
                                         `current_price` decimal(10,2) DEFAULT NULL,
                                         `sum` decimal(10,2) NOT NULL,
                                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                         `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `ollekassa_transfer`
--

DROP TABLE IF EXISTS `ollekassa_transfer`;
CREATE TABLE `ollekassa_transfer` (
                                      `id` int(11) NOT NULL,
                                      `member_id` int(11) NOT NULL,
                                      `convent_id` int(11) NOT NULL,
                                      `sum` decimal(10,2) NOT NULL,
                                      `comment` varchar(255) DEFAULT NULL,
                                      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                      `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
                            `session` varchar(40) NOT NULL DEFAULT '0',
                            `lastaccess` bigint(20) DEFAULT NULL,
                            `usr_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `staatused`
--

DROP TABLE IF EXISTS `staatused`;
CREATE TABLE `staatused` (
                             `id` int(11) NOT NULL,
                             `nimi` varchar(100) NOT NULL DEFAULT '',
                             `prefix` varchar(10) NOT NULL DEFAULT '',
                             `suffix` varchar(10) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

--
-- Tabeli struktuur tabelile `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
                         `id` int(11) NOT NULL,
                         `username` varchar(20) NOT NULL DEFAULT '',
                         `password` varchar(41) NOT NULL DEFAULT '',
                         `plugin` enum('mysql_old_password','mysql_native_password','plain') NOT NULL DEFAULT 'mysql_old_password',
                         `liikmed_id` int(11) NOT NULL DEFAULT 0,
                         `inactive` int(1) NOT NULL DEFAULT 0,
                         `lastlogin` datetime NOT NULL,
                         `jutukas_lastaccess` int(11) NOT NULL DEFAULT 0,
                         `jutukas_firstmess` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `users_rights`
--

DROP TABLE IF EXISTS `users_rights`;
CREATE TABLE `users_rights` (
                                `id_pk` int(11) NOT NULL,
                                `id` int(11) NOT NULL,
                                `code` varchar(20) NOT NULL DEFAULT '' COMMENT 'Õiguse nimetus (võrdne staatused_rights või ametid_rights tabelis olevaga)',
                                `selgitus` text NOT NULL COMMENT 'Lisa siia selgituseks, miks õigus on lisatud, sh kuupäev ja lisaja täisnimi'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `valved`
--

DROP TABLE IF EXISTS `valved`;
CREATE TABLE `valved` (
                          `id` int(11) NOT NULL,
                          `valve_tsyklid_id` int(11) NOT NULL DEFAULT 0,
                          `kuupaev` date NOT NULL DEFAULT '0000-00-00',
                          `aeg` tinyint(4) NOT NULL DEFAULT 0,
                          `liikmed_id` int(11) NOT NULL DEFAULT 0,
                          `majavan` tinyint(4) NOT NULL DEFAULT 0,
                          `puudus` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

-- --------------------------------------------------------

--
-- Tabeli struktuur tabelile `valve_tsyklid`
--

DROP TABLE IF EXISTS `valve_tsyklid`;
CREATE TABLE `valve_tsyklid` (
                                 `id` int(11) NOT NULL,
                                 `nimi` varchar(100) NOT NULL DEFAULT 'Valve',
                                 `valvajad` enum('koik','reb','tl','saun') NOT NULL DEFAULT 'koik',
                                 `koondised_id` int(11) NOT NULL DEFAULT 0,
                                 `algaeg` date NOT NULL DEFAULT '0000-00-00',
                                 `loppaeg` date NOT NULL DEFAULT '0000-00-00',
                                 `etapp` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_estonian_ci;

--
-- Indeksid tõmmistatud tabelitele
--

--
-- Indeksid tabelile `koondised`
--
ALTER TABLE `koondised`
    ADD PRIMARY KEY (`id`),
    ADD KEY `nimi` (`nimi`);

--
-- Indeksid tabelile `liikmed`
--
ALTER TABLE `liikmed`
    ADD PRIMARY KEY (`id`),
    ADD KEY `koondised_id` (`koondised_id`),
    ADD KEY `eesnimi` (`eesnimi`),
    ADD KEY `perenimi` (`perenimi`),
    ADD KEY `staatused_id` (`staatused_id`),
    ADD KEY `coet_a` (`coet_a`),
    ADD KEY `kustutatud` (`kustutatud`),
    ADD KEY `lahk_pohjused_id` (`lahk_pohjused_id`);

--
-- Indeksid tabelile `ollekassa_credit_netting`
--
ALTER TABLE `ollekassa_credit_netting`
    ADD PRIMARY KEY (`id`);

--
-- Indeksid tabelile `ollekassa_credit_netting_row`
--
ALTER TABLE `ollekassa_credit_netting_row`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_dit_netting_row_credit_netting_fk` (`credit_netting_id`),
    ADD KEY `FI_dit_netting_row_convent_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_member_credit`
--
ALTER TABLE `ollekassa_member_credit`
    ADD PRIMARY KEY (`id`),
    ADD KEY `member_id` (`member_id`),
    ADD KEY `FI_ber_credit_koondised_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_point_of_sale`
--
ALTER TABLE `ollekassa_point_of_sale`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI__created_by_fk` (`created_by`),
    ADD KEY `hash` (`hash`),
    ADD KEY `FI_ndised_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_product`
--
ALTER TABLE `ollekassa_product`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_duct_group_fk` (`product_group_id`);

--
-- Indeksid tabelile `ollekassa_product_group`
--
ALTER TABLE `ollekassa_product_group`
    ADD PRIMARY KEY (`id`);

--
-- Indeksid tabelile `ollekassa_product_info`
--
ALTER TABLE `ollekassa_product_info`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_duct_info_product_fk` (`product_id`),
    ADD KEY `FI_ndised_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_report`
--
ALTER TABLE `ollekassa_report`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_ber_fk` (`member_id`),
    ADD KEY `FI_ndised_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_report_row`
--
ALTER TABLE `ollekassa_report_row`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_duct_fk` (`product_id`),
    ADD KEY `FI_ort_fk` (`report_id`);

--
-- Indeksid tabelile `ollekassa_setting`
--
ALTER TABLE `ollekassa_setting`
    ADD PRIMARY KEY (`id`);

--
-- Indeksid tabelile `ollekassa_staatused_credit_limit`
--
ALTER TABLE `ollekassa_staatused_credit_limit`
    ADD PRIMARY KEY (`id`),
    ADD KEY `status_id` (`status_id`);

--
-- Indeksid tabelile `ollekassa_transaction`
--
ALTER TABLE `ollekassa_transaction`
    ADD PRIMARY KEY (`id`),
    ADD KEY `member_id` (`member_id`),
    ADD KEY `FI_duct_purchase_product_fk` (`product_id`),
    ADD KEY `FI_duct_purchase_created_by_fk` (`created_by`),
    ADD KEY `FI_nsaction_pos_fk` (`pos_id`),
    ADD KEY `FI_nsaction_convent_fk` (`convent_id`);

--
-- Indeksid tabelile `ollekassa_transfer`
--
ALTER TABLE `ollekassa_transfer`
    ADD PRIMARY KEY (`id`),
    ADD KEY `member_id` (`member_id`),
    ADD KEY `FI_nsfer_convent_fk` (`convent_id`),
    ADD KEY `FI_nsfer_created_by_fk` (`created_by`);

--
-- Indeksid tabelile `sessions`
--
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`session`),
    ADD KEY `usr_id` (`usr_id`);

--
-- Indeksid tabelile `staatused`
--
ALTER TABLE `staatused`
    ADD PRIMARY KEY (`id`),
    ADD KEY `nimi` (`nimi`);

--
-- Indeksid tabelile `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD KEY `username` (`username`),
    ADD KEY `liikmed_id` (`liikmed_id`);

--
-- Indeksid tabelile `users_rights`
--
ALTER TABLE `users_rights`
    ADD PRIMARY KEY (`id_pk`),
    ADD UNIQUE KEY `users_rights_uq` (`id`,`code`);

--
-- Indeksid tabelile `valved`
--
ALTER TABLE `valved`
    ADD PRIMARY KEY (`id`),
    ADD KEY `FI_kmed_fk` (`liikmed_id`),
    ADD KEY `FI_ve_tsyklid_fk` (`valve_tsyklid_id`);

--
-- Indeksid tabelile `valve_tsyklid`
--
ALTER TABLE `valve_tsyklid`
    ADD PRIMARY KEY (`id`),
    ADD KEY `algaeg` (`algaeg`),
    ADD KEY `loppaeg` (`loppaeg`),
    ADD KEY `etapp` (`etapp`),
    ADD KEY `koondised_id` (`koondised_id`);

--
-- AUTO_INCREMENT tõmmistatud tabelitele
--

--
-- AUTO_INCREMENT tabelile `koondised`
--
ALTER TABLE `koondised`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `liikmed`
--
ALTER TABLE `liikmed`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_credit_netting`
--
ALTER TABLE `ollekassa_credit_netting`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_credit_netting_row`
--
ALTER TABLE `ollekassa_credit_netting_row`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_member_credit`
--
ALTER TABLE `ollekassa_member_credit`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_point_of_sale`
--
ALTER TABLE `ollekassa_point_of_sale`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_product`
--
ALTER TABLE `ollekassa_product`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_product_group`
--
ALTER TABLE `ollekassa_product_group`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_product_info`
--
ALTER TABLE `ollekassa_product_info`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_report`
--
ALTER TABLE `ollekassa_report`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_report_row`
--
ALTER TABLE `ollekassa_report_row`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_setting`
--
ALTER TABLE `ollekassa_setting`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_staatused_credit_limit`
--
ALTER TABLE `ollekassa_staatused_credit_limit`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_transaction`
--
ALTER TABLE `ollekassa_transaction`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `ollekassa_transfer`
--
ALTER TABLE `ollekassa_transfer`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `staatused`
--
ALTER TABLE `staatused`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `users`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `users_rights`
--
ALTER TABLE `users_rights`
    MODIFY `id_pk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `valved`
--
ALTER TABLE `valved`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT tabelile `valve_tsyklid`
--
ALTER TABLE `valve_tsyklid`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tõmmistatud tabelite piirangud
--

--
-- Piirangud tabelile `ollekassa_credit_netting_row`
--
ALTER TABLE `ollekassa_credit_netting_row`
    ADD CONSTRAINT `credit_netting_row_credit_netting_fk` FOREIGN KEY (`credit_netting_id`) REFERENCES `ollekassa_credit_netting` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Piirangud tabelile `ollekassa_product`
--
ALTER TABLE `ollekassa_product`
    ADD CONSTRAINT `product_group_fk` FOREIGN KEY (`product_group_id`) REFERENCES `ollekassa_product_group` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Piirangud tabelile `ollekassa_product_info`
--
ALTER TABLE `ollekassa_product_info`
    ADD CONSTRAINT `product_info_product_fk` FOREIGN KEY (`product_id`) REFERENCES `ollekassa_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Piirangud tabelile `ollekassa_report_row`
--
ALTER TABLE `ollekassa_report_row`
    ADD CONSTRAINT `product_fk` FOREIGN KEY (`product_id`) REFERENCES `ollekassa_product` (`id`),
    ADD CONSTRAINT `report_row_product_fk` FOREIGN KEY (`product_id`) REFERENCES `ollekassa_product` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `report_row_report_fk` FOREIGN KEY (`report_id`) REFERENCES `ollekassa_report` (`id`) ON DELETE CASCADE;

--
-- Piirangud tabelile `ollekassa_transaction`
--
ALTER TABLE `ollekassa_transaction`
    ADD CONSTRAINT `product_purchase_product_fk` FOREIGN KEY (`product_id`) REFERENCES `ollekassa_product` (`id`),
    ADD CONSTRAINT `transaction_pos_fk` FOREIGN KEY (`pos_id`) REFERENCES `ollekassa_point_of_sale` (`id`);
COMMIT;
