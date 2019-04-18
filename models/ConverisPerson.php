<?php
/**
 * ConverisPerson.php - model class for persons from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 */

class ConverisPerson extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_persons';
        $config['has_many']['related_projects'] = [
            'class_name' => 'ConverisProjectPersonRelation',
            'foreign_key' => 'converis_id',
            'assoc_foreign_key' => 'person_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}