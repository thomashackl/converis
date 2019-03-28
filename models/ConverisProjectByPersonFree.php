<?php
/**
 * ConverisProjectByPersonFree.php - model class for free projects assigned to persons
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

class ConverisProjectByPersonFree extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'converis_projects_by_person_free';
        parent::configure($config);
    }

    public static function findByInstitute_id($institute_id)
    {
        $institute = Institute::find($institute_id);

        $names = [$institute->name];

        $query = "`interne_organisationen` LIKE :name";
        $params = ['name' => '%' . $institute->name . '%'];

        if ($institute->sub_institutes !== null && count($institute->sub_institutes) > 0) {
            $query .= " OR `interne_organisationen` REGEXP :sub";
            $params['sub']  = implode('|', array_merge($names, $institute->sub_institutes->pluck('name')));
        }

        $query .= " ORDER BY `kurzbezeichnung`";

        return self::findBySQL($query, $params);
    }

}