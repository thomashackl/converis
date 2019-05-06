<?php
/**
 * ConverisSourceOfFunds.php - model class for sources of funds from Converis
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
 * @property string source_id database column
 * @property string id alias column for source_id
 * @property string name database column
 * @property string short_name database column
 * @property string description database column
 * @property string website database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ConverisSourceOfFunds extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_sources_of_funds';
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
