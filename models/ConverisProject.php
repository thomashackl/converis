<?php
/**
 * ConverisProject.php - model class for research projects from Converis
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
 * @property int project_id database column
 * @property int converis_id database column
 * @property string type database column
 * @property string name database column
 * @property string long_name_1 database column
 * @property string long_name_2 database column
 * @property string public_title_1 database column
 * @property string public_title_2 database column
 * @property string short_description database column
 * @property string description_1 database column
 * @property string description_2 database column
 * @property string public_description_1 database column
 * @property string public_description_2 database column
 * @property string abstract_1 database column
 * @property string abstract_2 database column
 * @property string keywords_1 database column
 * @property string keywords_2 database column
 * @property string url database column
 * @property string start_date database column
 * @property string end_date database column
 * @property int status database column
 * @property int is_public database column
 * @property int application_id database column
 * @property string mkdate database column
 * @property string chdate database colum
 * @property string id computed column read/write
 * @property ConverisProjectStatus project_status has_one ConverisProjectStatus
 * @property ConverisProjectThirdPartyData third_party_data has_one ConverisProjectThirdPartyData
 * @property ConverisApplication application has_one ConverisApplication
 * @property SimpleORMapCollection related_organisations has_many ConverisProjectOrganisationRelation
 * @property SimpleORMapCollection related_persons has_many ConverisProjectPersonRelation
 * @property SimpleORMapCollection areas has_many ConverisProjectArea
 * @property SimpleORMapCollection related_sources_of_funds has_many ConverisSourceOfFundsRelation
 */

class ConverisProject extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_projects';
        $config['has_one']['project_status'] = [
            'class_name' => 'ConverisProjectStatus',
            'foreign_key' => 'status',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['has_one']['third_party_data'] = [
            'class_name' => 'ConverisProjectThirdPartyData',
            'foreign_key' => 'converis_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['has_one']['application'] = [
            'class_name' => 'ConverisApplication',
            'foreign_key' => 'application_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['has_many']['related_organisations'] = [
            'class_name' => 'ConverisProjectOrganisationRelation',
            'foreign_key' => 'converis_id',
            'assoc_foreign_key' => 'project_id'
        ];
        $config['has_many']['related_persons'] = [
            'class_name' => 'ConverisProjectPersonRelation',
            'foreign_key' => 'converis_id',
            'assoc_foreign_key' => 'project_id'
        ];
        $config['has_many']['areas'] = [
            'class_name' => 'ConverisArea',
            'thru_table' => 'converis_project_area',
            'thru_key' => 'area_id',
            'thru_assoc_key' => 'area_id',
            'order_by' => 'ORDER BY `name`',
        ];
        $config['has_many']['related_sources_of_funds'] = [
            'class_name' => 'ConverisProjectSourceOfFundsRelation',
            'foreign_key' => 'converis_id',
            'assoc_foreign_key' => 'project_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn(
            "SELECT GREATEST(MAX(`mkdate`), MAX(`chdate`)) FROM `" . self::config('db_table') . "`");
    }

}