<?php
/**
 * ConverisAdminInstitute.php
 * model class for assigning local admins to institutes
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

class ConverisAdminInstitute extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_admin_institute';
        parent::configure($config);
    }

}