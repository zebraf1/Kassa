<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1451306335.
 * Generated on 2015-12-28 14:38:55 by zebra
 */
class PropelMigration_1451306335
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ollekassa_member_credit_version`;

DROP TABLE IF EXISTS `ollekassa_product_purchase`;

ALTER TABLE `ollekassa_member_credit` DROP `version`;

ALTER TABLE `ollekassa_member_credit` DROP `version_created_at`;

ALTER TABLE `ollekassa_member_credit` DROP `version_created_by`;

CREATE INDEX `FI_kmed_fk` ON `valved` (`liikmed_id`);

CREATE INDEX `FI_ve_tsyklid_fk` ON `valved` (`valve_tsyklid_id`);

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
) ENGINE=InnoDB CHARACTER SET=\'utf8\';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ollekassa_transaction`;

ALTER TABLE `ollekassa_member_credit`
    ADD `version` INTEGER DEFAULT 0 AFTER `comment`,
    ADD `version_created_at` DATETIME AFTER `version`,
    ADD `version_created_by` VARCHAR(100) AFTER `version_created_at`;

DROP INDEX `FI_kmed_fk` ON `valved`;

DROP INDEX `FI_ve_tsyklid_fk` ON `valved`;

CREATE TABLE `ollekassa_member_credit_version`
(
    `id` INTEGER NOT NULL,
    `member_id` INTEGER NOT NULL,
    `credit` DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    `comment` VARCHAR(225) DEFAULT \'\',
    `version` INTEGER DEFAULT 0 NOT NULL,
    `version_created_at` DATETIME,
    `version_created_by` VARCHAR(100),
    PRIMARY KEY (`id`,`version`)
) ENGINE=MyISAM;

CREATE TABLE `ollekassa_product_purchase`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `member_id` INTEGER NOT NULL,
    `amount` DECIMAL(10,1) DEFAULT 0.0 NOT NULL,
    `current_price` DECIMAL(10,2),
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `FI_duct_purchase_product_fk` (`product_id`),
    INDEX `FI_duct_purchase_member_fk` (`member_id`),
    CONSTRAINT `product_purchase_product_fk`
        FOREIGN KEY (`product_id`)
        REFERENCES `ollekassa_product` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}