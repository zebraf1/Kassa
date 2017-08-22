<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1503430292.
 * Generated on 2017-08-22 21:31:32 by zebra
 */
class PropelMigration_1503430292
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

DROP TABLE IF EXISTS ollekassa_credit_netting;

CREATE TABLE `ollekassa_credit_netting`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET=\'utf8\';

DROP TABLE IF EXISTS ollekassa_credit_netting_row;

CREATE TABLE `ollekassa_credit_netting_row`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `credit_netting_id` INTEGER NOT NULL,
    `convent_id` INTEGER NOT NULL,
    `sum` DECIMAL(10,2) NOT NULL,
    `netting_done` INTEGER(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `FI_dit_netting_row_credit_netting_fk` (`credit_netting_id`),
    INDEX `FI_dit_netting_row_convent_fk` (`convent_id`),
    CONSTRAINT `credit_netting_row_credit_netting_fk`
        FOREIGN KEY (`credit_netting_id`)
        REFERENCES `ollekassa_credit_netting` (`id`)
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

DROP TABLE IF EXISTS `ollekassa_credit_netting`;

DROP TABLE IF EXISTS `ollekassa_credit_netting_row`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
