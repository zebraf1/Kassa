<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1469219453.
 * Generated on 2016-07-22 23:30:53 by zebra
 */
class PropelMigration_1469219453
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

CREATE INDEX `FI_ndised_alg_fk` ON `liikmed` (`koondised_alg`);

CREATE TABLE `ollekassa_product_info`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `product_id` INTEGER NOT NULL,
    `convent_id` INTEGER NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `status_id` VARCHAR(50) DEFAULT \'ACTIVE\',
    PRIMARY KEY (`id`),
    INDEX `FI_duct_info_product_fk` (`product_id`),
    INDEX `FI_ndised_fk` (`convent_id`),
    CONSTRAINT `product_info_product_fk`
        FOREIGN KEY (`product_id`)
        REFERENCES `ollekassa_product` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
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

DROP TABLE IF EXISTS `ollekassa_product_info`;

DROP INDEX `FI_ndised_alg_fk` ON `liikmed`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}