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
 *
 * @property string person_id database column
 * @property string id alias column for person_id
 * @property string username database column
 * @property string first_name database column
 * @property string last_name database column
 * @property string academic_title database column
 * @property string external database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection cards has_many ConverisCard
 */

class ConverisPerson extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_persons';
        $config['has_many']['cards'] = [
            'class_name' => 'ConverisCard',
            'assoc_foreign_key' => 'person_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

    public function getFullname()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        if ($this->academic_title != '') {
            $name = $this->academic_title . ' ' . $name;
        }
        return $name;
    }

}
