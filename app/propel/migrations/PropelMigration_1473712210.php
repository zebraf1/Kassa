<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1473712210.
 * Generated on 2016-09-12 23:30:10 by zebra
 */
class PropelMigration_1473712210
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

CREATE TABLE `ollekassa_setting`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `object` VARCHAR(20) NOT NULL,
    `object_id` INTEGER NOT NULL,
    `reference` VARCHAR(100) NOT NULL,
    `value` VARCHAR(100),
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

DROP TABLE IF EXISTS `ollekassa_setting`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}