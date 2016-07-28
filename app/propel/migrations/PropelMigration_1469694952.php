<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1469694952.
 * Generated on 2016-07-28 11:35:52 by zebra
 */
class PropelMigration_1469694952
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

ALTER TABLE `ollekassa_product` CHANGE `amount_type_id` `amount_type` VARCHAR(50) DEFAULT \'PIECE\';

ALTER TABLE `ollekassa_product` CHANGE `status_id` `status` VARCHAR(50) DEFAULT \'DISABLED\';

ALTER TABLE `ollekassa_product_info` CHANGE `status_id` `status` VARCHAR(50) DEFAULT \'DISABLED\';

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

ALTER TABLE `ollekassa_product` CHANGE `amount_type` `amount_type_id` VARCHAR(50) DEFAULT \'PIECE\';

ALTER TABLE `ollekassa_product` CHANGE `status` `status_id` VARCHAR(50) DEFAULT \'DISABLED\';

ALTER TABLE `ollekassa_product_info` CHANGE `status` `status_id` VARCHAR(50) DEFAULT \'DISABLED\';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}