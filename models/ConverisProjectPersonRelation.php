<?php
/**
 * ConverisProjectPersonRelation.php
 * model class for relation between projects and persons from Converis
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
 * @property int person_id database column
 * @property string type database column
 * @property int role database column
 * @property string start_date database column
 * @property string end_date database column
 * @property int junior_scientist database column
 * @property float contributed_share database column
 * @property float percentage_of_funding database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string id computed column read/write
 * @property SimpleORMapCollection projects has_and_belongs_to_many ConverisProject
 * @property SimpleORMapCollection persons has_and_belongs_to_many ConverisPerson
 */

class ConverisProjectPersonRelation extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'converis_project_person';
        $config['belongs_to']['projects'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['belongs_to']['persons'] = [
            'class_name' => 'ConverisPerson',
            'foreign_key' => 'person_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn(
            "SELECT GREATEST(MAX(`mkdate`), MAX(`chdate`)) FROM `" . self::config('db_table') . "`");
    }

}