<?php

class ProjectStatusSorting extends Migration {

    public function description()
    {
        return 'database field for custom sorting of project status values';
    }

    public function up()
    {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/../models');

        DBManager::get()->execute(
            "ALTER TABLE `converis_project_status` ADD `position` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `name_2`");

        ConverisProjectStatus::expireTableScheme();
    }

    public function down()
    {
        StudipAutoloader::addAutoloadPath(__DIR__ . '/../models');

        DBManager::get()->execute("ALTER TABLE `converis_project_status` DROP `position`");

        ConverisProjectStatus::expireTableScheme();
    }

}