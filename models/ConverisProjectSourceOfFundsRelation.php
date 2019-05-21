<?php
/**
 * ConverisProjectSourceOfFundsRelation.php
 * model class for relation between projects and organisations from Converis
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
 * @property int source_id database column
 * @property string amount database column
 * @property int order_source_of_funds database column
 * @property int order_project database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string id computed column read/write
 * @property ConverisSourceOfFunds source_of_funds belongs_to ConverisSourceOfFunds
 * @property ConverisProject project belongs_to ConverisProject
 */

class ConverisProjectSourceOfFundsRelation extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_project_source_of_funds';
        $config['belongs_to']['source_of_funds'] = [
            'class_name' => 'ConverisSourceOfFunds',
            'foreign_key' => 'source_id'
        ];
        $config['belongs_to']['project'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
