<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1429040327.
 * Generated on 2015-04-14 21:38:47 by zebra
 */
class PropelMigration_1429040327
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

ALTER TABLE `ollekassa_report`
    ADD `member_id` INTEGER AFTER `name`;

CREATE INDEX `FI_ber_fk` ON `ollekassa_report` (`member_id`);

ALTER TABLE `ollekassa_report` ADD CONSTRAINT `member_fk`
    FOREIGN KEY (`member_id`)
    REFERENCES `liikmed` (`id`);

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

ALTER TABLE `ollekassa_report` DROP FOREIGN KEY `member_fk`;

DROP INDEX `FI_ber_fk` ON `ollekassa_report`;

ALTER TABLE `ollekassa_report` DROP `member_id`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}