<?php

require_once(realpath(__DIR__ . '/../ConverisProjectsSyncCronjob.php'));

class AddOrderColumns extends Migration {

    public function up()
    {
        /*
         * Add new columns and truncate table so that new columns can be filled.
         */
        DBManager::get()->execute("ALTER TABLE `converis_project_area`
            ADD `order_area` INT NOT NULL DEFAULT '0' AFTER `area_id`,
            ADD `order_project` INT NOT NULL DEFAULT '0' AFTER `order_area`");
        DBManager::get()->execute("TRUNCATE TABLE `converis_project_area`");

        DBManager::get()->execute("ALTER TABLE `converis_project_card`
            ADD `order_card` INT NOT NULL DEFAULT '0' AFTER `percentage_of_funding`,
            ADD `order_project` INT NOT NULL DEFAULT '0' AFTER `order_card`");
        DBManager::get()->execute("TRUNCATE TABLE `converis_project_card`");

        DBManager::get()->execute("ALTER TABLE `converis_project_organisation`
            ADD `order_organisation` INT NOT NULL DEFAULT '0' AFTER `end_date`,
            ADD `order_project` INT NOT NULL DEFAULT '0' AFTER `order_organisation`");
        DBManager::get()->execute("TRUNCATE TABLE `converis_project_organisation`");

        DBManager::get()->execute("ALTER TABLE `converis_project_source_of_funds`
            ADD `order_source_of_funds` INT NOT NULL DEFAULT '0' AFTER `amount`,
            ADD `order_project` INT NOT NULL DEFAULT '0' AFTER `order_source_of_funds`");
        DBManager::get()->execute("TRUNCATE TABLE `converis_project_source_of_funds`");

        SimpleORMap::expireTableScheme();

        $job = new ConverisProjectsSyncCronjob();
        $job->execute('');
    }

    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `converis_project_area`
            DROP `order_area`, DROP `order_project`");
        DBManager::get()->execute("ALTER TABLE `converis_project_card`
            DROP `order_card`, DROP `order_project`");
        DBManager::get()->execute("ALTER TABLE `converis_project_organisation`
            DROP `order_organisation`, DROP `order_project`");
        DBManager::get()->execute("ALTER TABLE `converis_project_source_of_funds`
            DROP `order_source_of_funds`, DROP `order_project`");

        SimpleORMap::expireTableScheme();
    }

}