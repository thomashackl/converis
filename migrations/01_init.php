<?php

class Init extends Migration {

    public function up()
    {

        // Admin accounts (roots do always have full access)
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_admins`
        (
            `admin_id` INT NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(32) REFERENCES `auth_user_md5`.`username`,
            `type` ENUM('global', 'local') NOT NULL DEFAULT 'local',
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`admin_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Assign local admin accounts to institutes.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_admin_institute`
        (
            `admin_id` INT NOT NULL REFERENCES `converis_admins`.`admin_id`,
            `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id`,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`admin_id`, `institute_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Applications
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_applications`
        (
            `application_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `deadline` DATE NULL,
            `funding_amount` FLOAT(11,2) NULL,
            `funding_amount_cur` VARCHAR(100) NULL,
            `commentary_financial_data` TEXT NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`application_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Research projects.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_projects`
        (
            `project_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `type` ENUM ('free', 'third_party') NOT NULL,
            `name` VARCHAR(500) NOT NULL,
            `long_name_1` VARCHAR(500) NULL,
            `long_name_2` VARCHAR(500) NULL,
            `public_title_1` VARCHAR(500) NULL,
            `public_title_2` VARCHAR(500) NULL,
            `short_description` VARCHAR(1024) NULL,
            `description_1` TEXT NULL,
            `description_2` TEXT NULL,
            `public_description_1` TEXT NULL,
            `public_description_2` TEXT NULL,
            `abstract_1` VARCHAR(500) NULL,
            `abstract_2` VARCHAR(500) NULL,
            `keywords_1` VARCHAR(2000) NULL,
            `keywords_2` VARCHAR(2000) NULL,
            `url` VARCHAR(500) NULL,
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `status` INT NOT NULL DEFAULT 0,
            `is_public` INT NULL DEFAULT 0,
            `application_id` INT NULL REFERENCES `converis_appliactions`.`converis_id`,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`project_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Additional data for third party research projects.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_projects_third_party_data`
        (
            `project_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `project_number` VARCHAR(100) NULL,
            `project_type` INT NULL REFERENCES `converis_project_types`.`converis_id`,
            `doctoral_program` TINYINT(1) NOT NULL DEFAULT 0,
            `extension_until` DATETIME NULL,
            `stepped_into_running_project` DATETIME NULL,
            `date_exit_project` DATETIME NULL,
            `total_project_expenses` FLOAT(11,2) NULL,
            `total_project_expenses_cur` VARCHAR(100) NULL,
            `expenses_university` FLOAT(11,2) NULL,
            `expenses_university_cur` VARCHAR(100) NULL,
            `funding_quota` VARCHAR(10) NULL,
            `project_flat_charge` VARCHAR(255) NULL,
            `funding_central_resources` FLOAT(11,2) NULL,
            `funding_central_resources_cur` VARCHAR(100) NULL,
            `funding_chair` FLOAT(11,2) NULL,
            `funding_chair_cur` VARCHAR(100) NULL,
            `funding_third_party` FLOAT(11,2) NULL,
            `funding_third_party_cur` VARCHAR(100) NULL,
            `contract_sum_netto` FLOAT(11,2) NULL,
            `contract_sum_netto_cur` VARCHAR(100) NULL,
            `contract_sum_brutto` FLOAT(11,2) NULL,
            `contract_sum_brutto_cur` VARCHAR(100) NULL,
            `contract_tax_rate` FLOAT(5,2) NULL,
            `contract_tax` FLOAT(11,2) NULL,
            `contract_tax_cur` VARCHAR(100) NULL,
            `own_contribution` FLOAT(11,2) NULL,
            `own_contribution_cur` VARCHAR(100) NULL,
            `funding_amount` FLOAT(11,2) NULL,
            `funding_amount_cur` VARCHAR(100) NULL,
            `date_of_grant_agreement` DATETIME NULL,
            `commentary_funding` TEXT NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`project_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Names for project status.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_project_status`
        (
            `status_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `name_1` VARCHAR(100) NOT NULL,
            `name_2` VARCHAR(100) NOT NULL,
            PRIMARY KEY (`status_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Names for roles.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_roles`
        (
            `role_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `name_1` VARCHAR(100) NOT NULL,
            `name_2` VARCHAR(100) NULL,
            PRIMARY KEY (`role_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Names for project types.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_project_types`
        (
            `type_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `name_1` VARCHAR(100) NOT NULL,
            `name_2` VARCHAR(100) NULL,
            PRIMARY KEY (`type_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Organisations.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_organisations`
        (
            `organisation_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `converis_organisations_id` INT NOT NULL,
            `name_1` VARCHAR(255) NOT NULL,
            `name_2` VARCHAR(255) NULL,
            `short_description` VARCHAR(1024) NULL,
            `description` TEXT NULL,
            `address` VARCHAR(255) NULL,
            `street` VARCHAR(255) NULL,
            `postal_code` VARCHAR(7) NULL,
            `city` VARCHAR(255) NULL,
            `state` VARCHAR(100) NULL,
            `country` VARCHAR(255) NULL,
            `phone` VARCHAR(255) NULL,
            `fax` VARCHAR(255) NULL,
            `url` VARCHAR (255) NULL,
            `external` TINYINT(1) NOT NULL DEFAULT 0,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`organisation_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Areas.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_areas`
        (
            `area_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `area_type` VARCHAR(100) NULL,
            `name_1` VARCHAR(255) NOT NULL,
            `name_2` VARCHAR(255) NULL,
            `short_description` VARCHAR(1024) NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`area_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Persons
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_persons`
        (
            `person_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `username` VARCHAR(255) NULL,
            `first_name` VARCHAR(255) NOT NULL,
            `last_name` VARCHAR(255) NOT NULL,
            `academic_title` VARCHAR(100) NULL,
            `external` TINYINT(1) NOT NULL DEFAULT 0,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`person_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Cards
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_cards`
        (
            `card_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `person_id` INT NOT NULL REFERENCES `converis_persons`.`converis_id`,
            `external` TINYINT(1) NOT NULL DEFAULT 0,
            `address` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `fax` VARCHAR(255) NULL,
            `function` VARCHAR(100) NULL,
            `mobile` VARCHAR(255) NULL,
            `phone` VARCHAR(255) NULL,
            `url` VARCHAR(255) NULL,
            `organisation` VARCHAR(255) NULL,
            `payroll_lookup` VARCHAR(1024) NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`card_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Sources of funds.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_sources_of_funds`
        (
            `source_id` INT NOT NULL AUTO_INCREMENT,
            `converis_id` INT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `short_name` VARCHAR(100) NULL,
            `description` TEXT NULL,
            `website` VARCHAR(255) NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`source_id`),
            UNIQUE KEY (`converis_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Relation card - organisation
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_card_organisation`
        (
            `card_id` INT NOT NULL REFERENCES `converis_cards`.`converis_id`,
            `organisation_id` INT NOT NULL REFERENCES `converis_organisations`.`converis_id`,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`card_id`, `organisation_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Relation area - project
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_project_area`
        (
            `project_id` INT NOT NULL REFERENCES `converis_projects`.`converis_id`,
            `area_id` INT NOT NULL REFERENCES `converis_areas`.`converis_id`,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`project_id`, `area_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Relation project - card
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_project_card`
        (
            `project_id` INT NOT NULL REFERENCES `converis_projects`.`converis_id`,
            `card_id` INT NOT NULL REFERENCES `converis_cards`.`converis_id`,
            `type` ENUM ('internal', 'external'),
            `role` INT NULL REFERENCES `converis_roles`.`converis_id`,
            `start_date` DATE NULL,
            `end_date` DATE NULL,
            `junior_scientist` TINYINT(1) NOT NULL DEFAULT 0,
            `contributed_share` FLOAT(5,2) NULL,
            `percentage_of_funding` FLOAT(5,2) NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`project_id`, `card_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        // Relation project - source of funds
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `converis_project_source_of_funds`
        (
            `project_id` INT NOT NULL REFERENCES `converis_projects`.`converis_id`,
            `source_id` INT NOT NULL REFERENCES `converis_sources_of_funds`.`converis_id`,
            `amount` FLOAT(11,2) NULL,
            `mkdate` DATETIME(3) NOT NULL,
            `chdate` DATETIME(3) NOT NULL,
            PRIMARY KEY (`project_id`, `source_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");

        SimpleORMap::expireTableScheme();

        try {
            Config::get()->create('CONVERIS_HOSTNAME', [
                'value' => 'converis.host.name',
                'type' => 'string',
                'range' => 'global',
                'section' => 'converisplugin',
                'description' => 'Adresse des Converis-Servers'
            ]);
        } catch (InvalidArgumentException $e) {}
        try {
            Config::get()->create('CONVERIS_DB_USER', [
                'value' => 'username',
                'type' => 'string',
                'range' => 'global',
                'section' => 'converisplugin',
                'description' => 'Benutzername für die Datenbankverbindung'
            ]);
        } catch (InvalidArgumentException $e) {}
        try {
            Config::get()->create('CONVERIS_DB_PASSWORD', [
                'value' => 'secret',
                'type' => 'string',
                'range' => 'global',
                'section' => 'converisplugin',
                'description' => 'Passwort für die Datenbankverbindung'
            ]);
        } catch (InvalidArgumentException $e) {}
        try {
            Config::get()->create('CONVERIS_DATABASE', [
                'value' => 'converisdb',
                'type' => 'string',
                'range' => 'global',
                'section' => 'converisplugin',
                'description' => 'Name der Converis-Datenbank'
            ]);
        } catch (InvalidArgumentException $e) {}
        try {
            Config::get()->create('CONVERIS_REPORT_TEMPLATES', [
                'value' => json_encode([
                    'institute' => [
                        'pdf_fim' => [
                            'name' => 'Forschungsbericht FIM',
                            'action' => 'pdf_fim'
                        ]
                    ],
                    'user' => [
                        'xls_leistungsbezuege' => [
                            'name' => 'Leistungsbezüge (Excel)',
                            'type' => 'user',
                            'action' => 'xls_leistungsbezuege'
                        ],
                        'pdf_leistungsbezuege' => [
                            'name' => 'Leistungsbezüge (PDF)',
                            'type' => 'user',
                            'action' => 'pdf_leistungsbezuege'
                        ]
                    ]
                ]),
                'type' => 'array',
                'range' => 'global',
                'section' => 'converisplugin',
                'description' => 'Verfügbare Vorlage für Forschungsberichte'
            ]);
        } catch (InvalidArgumentException $e) {}
    }

    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `converis_projects_by_person_free`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `converis_projects_by_person_third_party`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `converis_projects_free`");
        DBManager::get()->execute("DROP TABLE IF EXISTS `converis_projects_third_party`");
    }

}