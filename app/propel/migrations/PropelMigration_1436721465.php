<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1436721465.
 * Generated on 2015-07-12 20:17:45 by zebra
 */
class PropelMigration_1436721465
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

ALTER TABLE `ollekassa_report_row` DROP FOREIGN KEY `report_fk`;

ALTER TABLE `ollekassa_report_row` ADD CONSTRAINT `report_row_report_fk`
    FOREIGN KEY (`report_id`)
    REFERENCES `ollekassa_report` (`id`)
    ON DELETE CASCADE;

ALTER TABLE `ollekassa_report_row` ADD CONSTRAINT `report_row_product_fk`
    FOREIGN KEY (`product_id`)
    REFERENCES `ollekassa_product` (`id`)
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

ALTER TABLE `ollekassa_report_row` DROP FOREIGN KEY `report_row_report_fk`;

ALTER TABLE `ollekassa_report_row` DROP FOREIGN KEY `report_row_product_fk`;

ALTER TABLE `ollekassa_report_row` ADD CONSTRAINT `report_fk`
    FOREIGN KEY (`report_id`)
    REFERENCES `ollekassa_report` (`id`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}