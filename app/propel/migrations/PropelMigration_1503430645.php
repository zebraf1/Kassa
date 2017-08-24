<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1503430645.
 * Generated on 2017-08-22 21:37:25 by zebra
 */
class PropelMigration_1503430645
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

ALTER TABLE `ollekassa_credit_netting_row` DROP FOREIGN KEY `credit_netting_row_credit_netting_fk`;

ALTER TABLE `ollekassa_credit_netting_row` ADD CONSTRAINT `credit_netting_row_credit_netting_fk`
    FOREIGN KEY (`credit_netting_id`)
    REFERENCES `ollekassa_credit_netting` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE;

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

ALTER TABLE `ollekassa_credit_netting_row` DROP FOREIGN KEY `credit_netting_row_credit_netting_fk`;

ALTER TABLE `ollekassa_credit_netting_row` ADD CONSTRAINT `credit_netting_row_credit_netting_fk`
    FOREIGN KEY (`credit_netting_id`)
    REFERENCES `ollekassa_credit_netting` (`id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}