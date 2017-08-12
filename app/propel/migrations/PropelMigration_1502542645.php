<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1502542645.
 * Generated on 2017-08-12 15:57:25 
 */
class PropelMigration_1502542645
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

RENAME TABLE `ollekassa_transfers` TO `ollekassa_transfer`;

ALTER TABLE `fos_user` CHANGE `username_canonical` `username_canonical` VARCHAR(255);

ALTER TABLE `fos_user` CHANGE `email_canonical` `email_canonical` VARCHAR(255);

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

RENAME TABLE `ollekassa_transfer` TO `ollekassa_transfers`;

ALTER TABLE `fos_user` CHANGE `username_canonical` `username_canonical` VARCHAR(191);

ALTER TABLE `fos_user` CHANGE `email_canonical` `email_canonical` VARCHAR(191);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}