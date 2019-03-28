<?php
/**
 * ConverisProjectsSyncCronjob.class.php
 *
 * Cronjob for syncing research projects from Converis DB.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Converis
 */

/**
 * Cron job for fetching database contents from Converis.
 */
class ConverisProjectsSyncCronjob extends CronJob {

    public static function getName() {
        return dgettext('converisplugin', 'Synchronisiere Forschungsprojekte aus Converis');
    }

    public static function getDescription() {
        return dgettext('garudaplugin', 'Holt alle Forschungsprojekte inkl. Daten aus Converis');
    }

    /**
     * Empty local database and fetch all content from Converis tables.
     */
    public function execute($last_result, $parameters = array()) {

        require_once(__DIR__ . '/models/ConverisProjectFree.php');
        require_once(__DIR__ . '/models/ConverisProjectThirdParty.php');
        require_once(__DIR__ . '/models/ConverisProjectByPersonFree.php');
        require_once(__DIR__ . '/models/ConverisProjectByPersonThirdParty.php');

        $host = Config::get()->CONVERIS_HOSTNAME;
        $database = Config::get()->CONVERIS_DATABASE;
        $username = Config::get()->CONVERIS_DB_USER;
        $password = Config::get()->CONVERIS_DB_PASSWORD;

        try {
            $db = new PDO(sprintf(
                'pgsql:dbname=%s;host=%s;user=%s;password=%s',
                $database, $host, $username, $password
            ));

            // Read data from Converis table X into Stud.IP table Y with model class Z
            $tables = [
                'pr_proj_free' => [
                        'model' => 'ConverisProjectFree',
                        'table' => 'converis_projects_free'
                    ],
                'pr_proj_third_party' => [
                        'model' => 'ConverisProjectThirdParty',
                        'table' => 'converis_projects_third_party'
                    ],
                'pr_all_free' => [
                        'model' => 'ConverisProjectByPersonFree',
                        'table' => 'converis_projects_by_person_free'
                    ],
                'pr_all_third_party' => [
                        'model' => 'ConverisProjectByPersonThirdParty',
                        'table' => 'converis_projects_by_person_free'
                    ]
            ];

            foreach ($tables as $converis => $studip) {

                $data = $db->query("SELECT * FROM " . $converis);
                $projects = $data->fetchAll(PDO::FETCH_ASSOC);

                // Clean old entries.
                DBManager::get()->exec("TRUNCATE TABLE `" . $studip['table'] . "`");

                // Import entries from Converis.
                foreach ($projects as $p) {
                    $object = $studip['model']::create($p);
                    $object->store();
                }

            }

        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}
