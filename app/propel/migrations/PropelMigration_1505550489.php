<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1505550489.
 * Generated on 2017-09-16 10:28:09 by zebra
 */
class PropelMigration_1505550489
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

CREATE TABLE `ollekassa_staatused_credit_limit`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `status_id` INTEGER NOT NULL,
    `credit_limit` INTEGER DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `status_id` (`status_id`)
) ENGINE=InnoDB CHARACTER SET=\'utf8\';

CREATE TABLE `staatused`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `nimi` VARCHAR(100) NOT NULL,
    `prefix` VARCHAR(10) NOT NULL,
    `suffix` VARCHAR(10) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET=\'utf8\';

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

DROP TABLE IF EXISTS `ollekassa_staatused_credit_limit`;

DROP TABLE IF EXISTS `staatused`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
