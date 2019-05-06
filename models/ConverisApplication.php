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
 * @property string application_id database column
 * @property string id alias column for application_id
 * @property string call_number database column
 * @property string start_date database column
 * @property string end_date database column
 * @property string duration_in_months database column
 * @property string deadline database column
 * @property string confirmation_of_receipt_date database column
 * @property string participation_role_id database column
 * @property string total_project_expenses database column
 * @property string total_project_expenses_cur database column
 * @property string expenses_upa database column
 * @property string expenses_upa_cur database column
 * @property string funding_amount database column
 * @property string funding_amount_cur database column
 * @property string funding_quota database column
 * @property string project_flat_charge database column
 * @property string own_contribution database column
 * @property string own_contribution_cur database column
 * @property string research_pool database column
 * @property string research_pool_cur database column
 * @property string funding_project_leader database column
 * @property string funding_project_leader_cur database column
 * @property string funding_third_party database column
 * @property string funding_third_party_cur database column
 * @property string commentary_financial_data database column
 * @property string university_is_applicant database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class ConverisApplication extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'converis_applications';
        $config['has_one']['participation_role'] = [
            'class_name' => 'ConverisRole',
            'foreign_key' => 'participation_role_id',
            'assoc_foreign_key' => 'role_id'
        ];
        parent::configure($config);
    }

    public static function getMaxTimestamp()
    {
        return DBManager::get()->fetchColumn("SELECT IFNULL(GREATEST(MAX(`mkdate`), MAX(`chdate`)), '1970-01-01')
            FROM `" . self::config('db_table') . "`");
    }

}
