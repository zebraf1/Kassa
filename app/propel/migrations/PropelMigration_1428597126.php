<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1428597126.
 * Generated on 2015-04-09 18:32:06 by zebra
 */
class PropelMigration_1428597126
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

DROP TABLE IF EXISTS `report_row`;

RENAME TABLE `product` TO `ollekassa_product`;

RENAME TABLE `report` TO `ollekassa_report`;

CREATE TABLE `ollekassa_report_row`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `report_id` INTEGER,
    `product_id` INTEGER,
    `amount` DECIMAL(10,1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `FI_duct_fk` (`product_id`),
    INDEX `FI_ort_fk` (`report_id`),
    CONSTRAINT `product_fk`
        FOREIGN KEY (`product_id`)
        REFERENCES `ollekassa_product` (`id`),
    CONSTRAINT `report_fk`
        FOREIGN KEY (`report_id`)
        REFERENCES `ollekassa_report` (`id`)
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

DROP TABLE IF EXISTS `ollekassa_report_row`;

RENAME TABLE `ollekassa_product` TO `product`;

RENAME TABLE `ollekassa_report` TO `report`;

CREATE TABLE `report_row`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `report_id` INTEGER,
    `product_id` INTEGER,
    `amount` DECIMAL(10,1) DEFAULT 0.0 NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `FI_duct_fk` (`product_id`),
    INDEX `FI_ort_fk` (`report_id`)
) ENGINE=MyISAM;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}