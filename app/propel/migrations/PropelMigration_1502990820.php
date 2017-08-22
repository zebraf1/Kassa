<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1502990820.
 * Generated on 2017-08-17 20:27:00 
 */
class PropelMigration_1502990820
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

ALTER TABLE `ollekassa_member_credit`
    ADD `convent_id` INTEGER NOT NULL AFTER `member_id`;
    
CREATE INDEX `FI_ber_credit_koondised_fk` ON `ollekassa_member_credit` (`convent_id`);

UPDATE ollekassa_member_credit SET convent_id = 6;

CREATE TABLE `ollekassa_credit_netting`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `ollekassa_credit_netting_row`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `credit_netting_id` INTEGER NOT NULL,
    `convent_id` INTEGER NOT NULL,
    `sum` DECIMAL(10,2) NOT NULL,
    `netting_done` INTEGER(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `FI_dit_netting_row_credit_netting_fk` (`credit_netting_id`),
    INDEX `FI_dit_netting_row_convent_fk` (`convent_id`)
) ENGINE=MyISAM;

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

DROP INDEX `FI_ber_credit_koondised_fk` ON `ollekassa_member_credit`;

ALTER TABLE `ollekassa_member_credit` DROP `convent_id`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
