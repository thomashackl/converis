<?php
/**
 * ConverisRole.php - model class for roles from Converis
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    ConverisProjects
 * @property string role_id database column
 * @property string id alias column for role_id
 * @property string converis_id database column
 * @property string name_1 database column
 * @property string name_2 database column
 */

class ConverisRole extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_roles';
        parent::configure($config);
    }

}
