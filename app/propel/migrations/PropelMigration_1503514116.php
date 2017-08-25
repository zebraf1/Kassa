<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1503514116.
 * Generated on 2017-08-23 21:48:36 by kasutaja
 */
class PropelMigration_1503514116
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

DROP INDEX `FI_nsaction_pos_fk` ON `ollekassa_transaction`;

ALTER TABLE `ollekassa_transaction` DROP FOREIGN KEY `transaction_pos_fk`;

ALTER TABLE `ollekassa_transaction` DROP `pos_id`;


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

ALTER TABLE `ollekassa_transaction`
    ADD `pos_id` INTEGER AFTER `member_id`;

CREATE INDEX `FI_nsaction_pos_fk` ON `ollekassa_transaction` (`pos_id`);

ALTER TABLE `ollekassa_transaction` ADD CONSTRAINT `transaction_pos_fk`
    FOREIGN KEY (`pos_id`)
    REFERENCES `ollekassa_point_of_sale` (`id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}