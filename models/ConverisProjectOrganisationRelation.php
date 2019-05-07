<?php
/**
 * ConverisProjectOrganisationRelation.php
 * model class for relation between projects and cards from Converis
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
 * @property string project_id database column
 * @property string organisation_id database column
 * @property string type database column
 * @property string role_id database column
 * @property string start_date database column
 * @property string end_date database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string id computed column read/write
 * @property ConverisProject project belongs_to ConverisProject
 * @property ConverisOrganisation card belongs_to ConverisOrganisation
 */

class ConverisProjectOrganisationRelation extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_project_organisation';
        $config['belongs_to']['project'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id'
        ];
        $config['belongs_to']['organisation'] = [
            'class_name' => 'ConverisOrganisation',
            'foreign_key' => 'organisation_id'
        ];
        $config['has_one']['role'] = [
            'class_name' => 'ConverisRole',
            'foreign_key' => 'role_id',
            'assoc_foreign_key' => 'role_id'
        ];
        $config['additional_fields']['organisation_name']['get'] = function ($oRelation) {
            return $oRelation->organisation->name_1;
        };
        $config['additional_fields']['project_name']['get'] = function ($pcRelation) {
            return $pcRelation->project->name;
        };
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
