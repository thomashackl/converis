<?php
/**
 * ConverisApplication.php - model class for research project applications from Converis
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
 * @property int application_id database column
 * @property int converis_id database column
 * @property string start_date database column
 * @property string end_date database column
 * @property string deadline database column
 * @property string funding_amount database column
 * @property string funding_amount_cur database column
 * @property string commentary_financial_data database column
 * @property string mkdate database column
 * @property string chdate database colum
 * @property string id computed column read/write
 */

class ConverisApplication extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'converis_applications';
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn(
            "SELECT GREATEST(MAX(`mkdate`), MAX(`chdate`)) FROM `" . self::config('db_table') . "`");
    }

}