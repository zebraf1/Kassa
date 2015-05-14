<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1418068634.
 * Generated on 2014-12-08 20:57:14 by zebra
 */
class PropelMigration_1418068634
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

ALTER TABLE `product` CHANGE `price` `price` DECIMAL(10,2) NOT NULL;

ALTER TABLE `report` CHANGE `created_at` `created_at` DATE NOT NULL;

ALTER TABLE `report` DROP `updated_at`;

ALTER TABLE `report_row` CHANGE `amount` `amount` DECIMAL(10,1) DEFAULT 0 NOT NULL;

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

ALTER TABLE `product` CHANGE `price` `price` DECIMAL(10,0) NOT NULL;

ALTER TABLE `report` CHANGE `created_at` `created_at` DATETIME;

ALTER TABLE `report`
    ADD `updated_at` DATETIME AFTER `created_at`;

ALTER TABLE `report_row` CHANGE `amount` `amount` DECIMAL(10,0) DEFAULT 0 NOT NULL;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}