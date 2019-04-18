<?php
/**
 * ConverisProjectCardRelation.php
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
 * @property int project_id database column
 * @property int card_id database column
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

class ConverisProjectCardRelation extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_project_card';
        $config['belongs_to']['project'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['belongs_to']['card'] = [
            'class_name' => 'ConverisCard',
            'foreign_key' => 'card_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['has_one']['role_object'] = [
            'class_name' => 'ConverisRole',
            'foreign_key' => 'role',
            'assoc_foreign_key' => 'converis_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}