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
 */

class ConverisCard extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_cards';
        $config['belongs_to']['person'] = [
            'class_name' => 'ConverisPerson',
            'foreign_key' => 'person_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['belongs_to']['organisation'] = [
            'class_name' => 'ConverisOrganisation',
            'thru_table' => 'converis_card_organisation',
            'thru_key' => 'organisation_id',
            'thru_assoc_key' => 'converis_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}