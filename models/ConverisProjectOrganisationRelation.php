<?php
/**
 * ConverisProjectOrganisationRelation.php
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
 */

class ConverisProjectOrganisationRelation extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_project_organisation';
        $config['has_one']['role_object'] = [
            'class_name' => 'ConverisRole',
            'foreign_key' => 'role',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['belongs_to']['project'] = [
            'class_name' => 'ConverisProject',
            'foreign_key' => 'project_id',
            'assoc_foreign_key' => 'converis_id'
        ];
        $config['belongs_to']['organisation'] = [
            'class_name' => 'ConverisOrganisation',
            'foreign_key' => 'organisation_id',
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