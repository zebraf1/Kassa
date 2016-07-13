<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1468444273.
 * Generated on 2016-07-14 00:11:13 by zebra
 */
class PropelMigration_1468444273
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

ALTER TABLE `ollekassa_product`
    ADD `product_group_id` INTEGER AFTER `seq`;

CREATE INDEX `FI_duct_group_fk` ON `ollekassa_product` (`product_group_id`);

ALTER TABLE `ollekassa_product` ADD CONSTRAINT `product_group_fk`
    FOREIGN KEY (`product_group_id`)
    REFERENCES `ollekassa_product_group` (`id`)
    ON DELETE CASCADE;

CREATE TABLE `ollekassa_product_group`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `seq` INTEGER(3) DEFAULT 1,
    PRIMARY KEY (`id`)
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

DROP TABLE IF EXISTS `ollekassa_product_group`;

ALTER TABLE `ollekassa_product` DROP FOREIGN KEY `product_group_fk`;

DROP INDEX `FI_duct_group_fk` ON `ollekassa_product`;

ALTER TABLE `ollekassa_product` DROP `product_group_id`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}