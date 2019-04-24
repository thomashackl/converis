<?php
/**
 * ConverisOrganisation.php - model class for organisations from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 *
 * @property string organisation_id database column
 * @property string id alias column for organisation_id
 * @property string converis_id database column
 * @property string converis_organisations_id database column
 * @property string name_1 database column
 * @property string name_2 database column
 * @property string short_description database column
 * @property string description database column
 * @property string address database column
 * @property string street database column
 * @property string postal_code database column
 * @property string city database column
 * @property string state database column
 * @property string country database column
 * @property string phone database column
 * @property string fax database column
 * @property string url database column
 * @property string external database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection cards has_many ConverisCard
 */

class ConverisOrganisation extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_organisations';
        $config['has_many']['cards'] = [
            'class_name' => 'ConverisCard',
            'thru_table' => 'converis_card_organisation',
            'thru_key' => 'organisation_id'
        ];
        parent::configure($config);
    }

    /**
     * Finds all organisations related to the given project
     * via the cards associated to the project.
     *
     * @param int $project_id the associated project
     * @param string $type one of 'all', 'internal', 'external': gets only organisations associated in the given type
     * @return mixed
     */
    public static function findByProject_id($project_id, $type = 'all')
    {
        $organisations = DBManager::get()->fetchAll("SELECT DISTINCT o.*
            FROM `converis_organisations` o
                JOIN `converis_card_organisation` co ON (co.`organisation_id` = o.`converis_id`)
                JOIN `converis_project_card` pc ON (pc.`card_id` = co.`card_id`)
                JOIN `converis_projects` p ON (p.`converis_id` = pc.`project_id`)
            WHERE p.`project_id` = :id
                AND pc.`type` IN (:types)
            ORDER BY o.`name_1`",
            [
                'id' => $project_id,
                'types' => [ $type === 'all' ? "'internal', 'extermal'" : $type ]
            ],
            __CLASS__ . '::buildExisting'
        );

        return $organisations;
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
