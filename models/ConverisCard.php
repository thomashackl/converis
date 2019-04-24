<?php
/**
 * ConverisCard.php - model class for cards from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 * @property string card_id database column
 * @property string id alias column for card_id
 * @property string converis_id database column
 * @property string person_id database column
 * @property string external database column
 * @property string address database column
 * @property string email database column
 * @property string fax database column
 * @property string function database column
 * @property string mobile database column
 * @property string phone database column
 * @property string url database column
 * @property ConverisOrganisation organisation belongs_to ConverisOrganisation
 * @property string payroll_lookup database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMapCollection related_projects has_many ConverisProjectCardRelation
 * @property ConverisPerson person belongs_to ConverisPerson
 */

class ConverisCard extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_cards';
        $config['belongs_to']['person'] = [
            'class_name' => 'ConverisPerson',
            'foreign_key' => 'person_id'
        ];
        $config['has_many']['related_projects'] = [
            'class_name' => 'ConverisProjectCardRelation',
            'foreign_key' => 'card_id'
        ];
        $config['has_and_belongs_to_many']['organisation'] = [
            'class_name' => 'ConverisOrganisation',
            'thru_table' => 'converis_card_organisation',
            'thru_key' => 'organisation_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
