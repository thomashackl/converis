<?php
/**
 * ConverisAdmin.php
 * model class for admin accounts who may see and manage everything
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
 */

class ConverisAdmin extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_admins';
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['has_and_belongs_to_many']['institutes'] = [
            'class_name' => 'Institute',
            'thru_table' => 'converis_admin_institute',
            'thru_assoc_key' => 'institute_id',
            'order_by' => 'ORDER BY `name`',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        parent::configure($config);
    }

}
